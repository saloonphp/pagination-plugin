<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Paginators;

use Iterator;
use Saloon\Helpers\Helpers;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Contracts\Connector;
use Illuminate\Support\LazyCollection;
use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\SaloonPagination\Traits\HasAsyncPagination;

abstract class Paginator implements Iterator
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
     * Optional maximum number of pages the paginator is limited to
     */
    protected ?int $maxPages = null;

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
            ->onResponse(static fn (Response $response) => $response->throw())
            ->onResponse(fn (Response $response) => $this->totalResults += count($this->getPageItems($response)));
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

        $this->onNext($this->currentResponse);
    }

    /**
     * Apply additional logic on the next page
     */
    protected function onNext(Response $response): void
    {
        //
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
            return $this->page < $this->getTotalPages($this->currentResponse);
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
            foreach ($this->getPageItems($response) as $item) {
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
    abstract protected function getPageItems(Response $response): array;
}
