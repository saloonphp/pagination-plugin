<?php

declare(strict_types=1);

namespace Saloon\PaginationPlugin;

use Iterator;
use Countable;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Http\Connector;
use Saloon\Helpers\Helpers;
use InvalidArgumentException;
use Illuminate\Support\LazyCollection;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\PaginationPlugin\Contracts\Paginatable;
use Saloon\PaginationPlugin\Traits\HasAsyncPagination;
use Saloon\PaginationPlugin\Exceptions\PaginationException;
use Saloon\PaginationPlugin\Contracts\MapPaginatedResponseItems;

abstract class Paginator implements Iterator, Countable
{
    /**
     * The connector being paginated
     */
    protected Connector $connector;

    /**
     * The request being paginated
     */
    protected Request $request;

    /**
     * The start page when the paginator is rewound
     */
    protected int $startPage = 1;

    /**
     * Internal Marker For The Current Page
     *
     * @deprecated Use $currentPage instead
     */
    protected int $page = 1;

    /**
     * Denotes the current page
     */
    protected int $currentPage = 0;

    /**
     * When using async this is the total number of pages
     */
    protected ?int $totalPages = null;

    /**
     * Optional maximum number of pages the paginator is limited to
     */
    protected ?int $maxPages = null;

    /**
     * The limit of results per-page
     */
    protected ?int $perPageLimit = null;

    /**
     * The current response on the paginator
     */
    protected ?Response $currentResponse = null;

    /**
     * Total results that have been paginated through
     */
    protected int $totalResults = 0;

    /**
     * Should the pagination plugin check if there is an infinite loop
     */
    protected bool $detectInfiniteLoop = true;

    /**
     * The last five response body checksums
     *
     * Used to determine if an infinite loop is happening
     *
     * @var array<int, string>
     */
    protected array $lastFiveBodyChecksums = [];

    /**
     * Constructor
     */
    public function __construct(Connector $connector, Request $request)
    {
        if (! $request instanceof Paginatable) {
            throw new InvalidArgumentException(sprintf('The request must implement the `%s` interface to be used on paginators.', Paginatable::class));
        }

        $this->connector = $connector;
        $this->request = clone $request;

        // We'll register two middleware. One will force any requests to throw an exception
        // if the request fails. This will prevent the rest of our paginator to keep
        // on iterating if something goes wrong. The second middleware allows us
        // to increment the total results which can be used to check if we
        // are at the end of a page.

        $this->request->middleware()
            ->onResponse(static fn (Response $response): Response => $response->throw())
            ->onResponse(function (Response $response): void {
                $request = $response->getRequest();

                $pageItems = $request instanceof MapPaginatedResponseItems
                    ? $request->mapPaginatedResponseItems($response)
                    : $this->getPageItems($response, $request);

                $this->totalResults += count($pageItems);
            })
            ->onResponse(function (Response $response): void {
                if ($this->detectInfiniteLoop === false || $this->isAsyncPaginationEnabled() === true) {
                    return;
                }

                // We'll start by creating a checksum of the body and appending
                // it to the array of checksums. If the last five checksums
                // are the same then we will throw an exception.

                $this->lastFiveBodyChecksums[] = $this->getBodyChecksum($response);

                if (count($this->lastFiveBodyChecksums) < 5) {
                    return;
                }

                $allValuesAreTheSame = count(array_unique($this->lastFiveBodyChecksums, SORT_REGULAR)) === 1;

                // When there are five items in the array are the same, we'll throw an exception.

                if ($allValuesAreTheSame === true) {
                    throw new PaginationException(
                        'Potential infinite loop detected! The last 5 requests have had exactly the same body. You can use the $detectInfiniteLoop property on your paginator to disable this check.'
                    );
                }

                // If all the values are not the same we will simply remove the
                // oldest item from the array (the first)

                array_shift($this->lastFiveBodyChecksums);
            });
    }

    /**
     * Get the current request
     */
    public function current(): Response|PromiseInterface
    {
        $request = $this->applyPagination(clone $this->request);

        if ($this->isAsyncPaginationEnabled() === false) {
            return $this->currentResponse = $this->connector->send($request);
        }

        $promise = $this->connector->sendAsync($request);

        // When the iterator is at the beginning, we need to force the first response to come
        // back right away, so we can calculate the next pages we need to get.

        if (is_null($this->currentResponse)) {
            $this->currentResponse = $promise->wait();
        }

        return $promise;
    }

    /**
     * Move to the next page
     */
    public function next(): void
    {
        $this->page++;
        $this->currentPage++;
    }

    /**
     * Get the key of the paginator
     */
    public function key(): int
    {
        return $this->currentPage;
    }

    /**
     * Check if the paginator has another page to retrieve
     */
    public function valid(): bool
    {
        if (isset($this->maxPages) && ($this->currentPage + 1) > $this->maxPages) {
            return false;
        }

        if (is_null($this->currentResponse)) {
            return true;
        }

        if ($this->isAsyncPaginationEnabled()) {
            return ($this->currentPage + 1) <= $this->totalPages ??= $this->getTotalPages($this->currentResponse);
        }

        return $this->isLastPage($this->currentResponse) === false;
    }

    /**
     * Rewind the iterator
     */
    public function rewind(): void
    {
        $this->currentPage = max(0, $this->startPage - 1);
        $this->page = $this->startPage;
        $this->currentResponse = null;
        $this->totalResults = 0;
        $this->totalPages = null;
        $this->onRewind();
    }

    /**
     * Apply additional logic on rewind
     *
     * This may be resetting specific variables on the paginator classes
     */
    protected function onRewind(): void
    {
        //
    }

    /**
     * Iterate through the paginator items
     *
     * @return iterable<mixed, Response|PromiseInterface>
     */
    public function items(): iterable
    {
        if ($this->isAsyncPaginationEnabled()) {
            foreach ($this as $promise) {
                yield $promise;
            }

            return;
        }

        /** @var Response $response */
        foreach ($this as $response) {
            $request = $response->getRequest();

            $pageItems = $request instanceof MapPaginatedResponseItems
                ? $request->mapPaginatedResponseItems($response)
                : $this->getPageItems($response, $request);

            foreach ($pageItems as $item) {
                yield $item;
            }
        }
    }

    /**
     * Create a collection from the items
     */
    public function collect(bool $throughItems = true): LazyCollection
    {
        return LazyCollection::make(function () use ($throughItems): iterable {
            return $throughItems ? yield from $this->items() : yield from $this;
        });
    }

    /**
     * Get the total number of results
     */
    public function getTotalResults(): int
    {
        return $this->totalResults;
    }

    /**
     * Check if async pagination is enabled
     */
    public function isAsyncPaginationEnabled(): bool
    {
        return in_array(HasAsyncPagination::class, Helpers::classUsesRecursive($this), true)
            && method_exists($this, 'getTotalPages')
            && property_exists($this, 'async')
            && $this->async === true;
    }

    /**
     * Set the maximum number of pages the paginator will iterate over
     */
    public function setMaxPages(?int $maxPages): static
    {
        $this->maxPages = $maxPages;

        return $this;
    }

    /**
     * Set the per-page limit on the response
     */
    public function setPerPageLimit(?int $perPageLimit): static
    {
        $this->perPageLimit = $perPageLimit;

        return $this;
    }

    /**
     * Get the original request passed into the paginator
     */
    public function getOriginalRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get page
     *
     * @deprecated Use currentPage() instead
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Get the current page
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Set the start page of the paginator
     *
     * Used when the paginator is rewound
     */
    public function setStartPage(int $startPage): static
    {
        $this->startPage = $startPage;

        return $this;
    }

    /**
     * Count the iterator
     */
    public function count(): int
    {
        $this->rewind();

        // When asynchronous pagination is enabled, we can call the `getTotalPages`
        // method to count the number of pages. This reduces the number of API
        // calls that we need to make.

        if ($this->isAsyncPaginationEnabled() === true) {
            /** @var PromiseInterface $promise */
            $promise = $this->current();

            return $this->getTotalPages($promise->wait());
        }

        // We are unable to use `iterator_count` because that method only calls
        // the `next` and `valid` methods on the iterator, and it assumes the
        // state of loaded items already exists - so in order to keep memory
        // usage low, we should just iterate through each item and count.

        $count = 0;

        foreach ($this as $ignored) {
            $count++;
        }

        return $count;
    }

    /**
     * Get the checksum from the response body
     */
    protected function getBodyChecksum(Response $response): string
    {
        $temporaryResource = $response->getRawStream();

        $context = hash_init('md5');
        hash_update_stream($context, $temporaryResource);

        $checksum = hash_final($context);

        fclose($temporaryResource);

        return $checksum;
    }

    /**
     * Apply the pagination to the request
     */
    abstract protected function applyPagination(Request $request): Request;

    /**
     * Check if we are on the last page
     */
    abstract protected function isLastPage(Response $response): bool;

    /**
     * Get the results from the page
     *
     * @return array<mixed, mixed>
     */
    abstract protected function getPageItems(Response $response, Request $request): array;
}
