<?php

declare(strict_types=1);

namespace Sammyjo20\Package\Paginators;

use Iterator;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Contracts\Connector;
use Illuminate\Support\LazyCollection;

abstract class Paginator implements Iterator
{
    /**
     * The connector being paginated
     *
     * @var Connector
     */
    protected Connector $connector;

    /**
     * The request being paginated
     *
     * @var Request
     */
    protected Request $request;

    /**
     * Internal Marker For The Current Page
     *
     * @var int
     */
    protected int $page = 1;

    /**
     * The current response on the paginator
     *
     * @var Response|null
     */
    protected ?Response $currentResponse = null;

    /**
     * Total results that have been paginated through
     *
     * @var int
     */
    protected int $totalResults = 0;

    /**
     * @param Connector $connector
     * @param Request $request
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
     *
     * @return Response
     */
    public function current(): Response
    {
        return $this->currentResponse = $this->connector->send(
            $this->applyPagination(clone $this->request)
        );
    }

    /**
     * Move to the next page
     *
     * @return void
     */
    public function next(): void
    {
        $this->page++;

        $this->onNext($this->currentResponse);
    }

    /**
     * Apply additional logic on the next page
     *
     * @param Response $response
     * @return void
     */
    protected function onNext(Response $response): void
    {
        //
    }

    /**
     * Get the key of the paginator
     *
     * @return int
     */
    public function key(): int
    {
        return $this->page - 1;
    }

    /**
     * Check if the paginator has another page to retrieve
     *
     * @return bool
     */
    public function valid(): bool
    {
        if (is_null($this->currentResponse)) {
            return true;
        }

        return $this->isLastPage($this->currentResponse) === false;
    }

    /**
     * Rewind the iterator
     *
     * @return void
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
     *
     * @return void
     */
    protected function onRewind(): void
    {
        //
    }

    /**
     * Iterate through the paginator items
     *
     * @return iterable
     */
    public function items(): iterable
    {
        foreach ($this as $response) {
            foreach ($this->getPageItems($response) as $item) {
                yield $item;
            }
        }
    }

    /**
     * Create a collection from the items
     *
     * @return LazyCollection
     */
    public function collect(): LazyCollection
    {
        return LazyCollection::make(fn () => yield from $this->items());
    }

    /**
     * @return int
     */
    public function getTotalResults(): int
    {
        return $this->totalResults;
    }

    /**
     * Apply the pagination to the request
     *
     * @param Request $request
     * @return Request
     */
    abstract protected function applyPagination(Request $request): Request;

    /**
     * Check if we are on the last page
     *
     * @param Response $response
     * @return bool
     */
    abstract protected function isLastPage(Response $response): bool;

    /**
     * Get the results from the page
     *
     * @param Response $response
     * @return array
     */
    abstract protected function getPageItems(Response $response): array;
}
