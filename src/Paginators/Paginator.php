<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Paginators;

use Closure;
use Countable;
use Iterator;
use Saloon\Helpers\Helpers;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Contracts\Connector;
use Illuminate\Support\LazyCollection;
use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\SaloonPagination\Traits\HasAsyncPagination;

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
     * Internal Marker For The Current Page
     */
    protected int $page = 1;

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
     * Constructor
     */
    public function __construct(Connector $connector, Request $request)
    {
        $this->connector = clone $connector;
        $this->request = clone $request;

        // We'll register two middleware. One will force any requests to throw an exception
        // if the request fails. This will prevent the rest of our paginator to keep
        // on iterating if something goes wrong. The second middleware allows us
        // to increment the total results which can be used to check if we
        // are at the end of a page.

        $this->connector->middleware()
            ->onResponse(static fn(Response $response) => $response->throw())
            ->onResponse(function (Response $response) {
                $pageItems = $this->getPageItems($response, $response->getRequest());

                return $this->totalResults += count($pageItems);
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
    }

    /**
     * Get the key of the paginator
     */
    public function key(): int
    {
        return $this->page - 1;
    }

    /**
     * Check if the paginator has another page to retrieve
     */
    public function valid(): bool
    {
        if (isset($this->maxPages) && $this->page > $this->maxPages) {
            return false;
        }

        if (is_null($this->currentResponse)) {
            return true;
        }

        if ($this->isAsyncPaginationEnabled()) {
            return $this->page <= $this->totalPages ??= $this->getTotalPages($this->currentResponse);
        }

        return $this->isLastPage($this->currentResponse) === false;
    }

    /**
     * Rewind the iterator
     */
    public function rewind(): void
    {
        $this->page = 1;
        $this->currentResponse = null;
        $this->totalResults = 0;
        $this->totalPages = 0;
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
     */
    public function items(): iterable
    {
        if ($this->isAsyncPaginationEnabled()) {
            foreach ($this as $promise) {
                yield $promise;
            }

            return;
        }

        foreach ($this as $response) {
            $pageItems = $this->getPageItems($response, $response->getRequest());

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
    public function setMaxPages(?int $maxPages): Paginator
    {
        $this->maxPages = $maxPages;

        return $this;
    }

    /**
     * Set the per-page limit on the response
     */
    public function setPerPageLimit(?int $perPageLimit): Paginator
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
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Count the iterator
     *
     * @return int
     */
    public function count(): int
    {
        $this->rewind();

        // When asynchronous pagination is enabled, we can call the `getTotalPages`
        // method to count the number of pages. This reduces the number of API
        // calls that we need to make.

        if ($this->isAsyncPaginationEnabled() === true) {
            return $this->getTotalPages($this->current()->wait());
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
     * Apply the pagination to the request
     */
    abstract protected function applyPagination(Request $request): Request;

    /**
     * Check if we are on the last page
     */
    abstract protected function isLastPage(Response $response): bool;

    /**
     * Get the results from the page
     */
    abstract protected function getPageItems(Response $response, Request $request): array;
}
