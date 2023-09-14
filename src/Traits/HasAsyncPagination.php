<?php

declare(strict_types=1);

namespace Saloon\PaginationPlugin\Traits;

use Saloon\Http\Pool;
use Saloon\Http\Response;

trait HasAsyncPagination
{
    /**
     * Determines if async is enabled or not
     */
    protected bool $async = false;

    /**
     * Enable or disable async pagination
     *
     * @return $this
     */
    public function async(bool $async = true): static
    {
        $this->async = $async;

        return $this;
    }

    /**
     * Create an async pool for the iterator
     */
    public function pool(callable|int $concurrency = 5, ?callable $responseHandler = null, ?callable $exceptionHandler = null): Pool
    {
        /** @var iterable<mixed, \Saloon\Contracts\Request> $iterator */
        $iterator = clone $this->async();

        return $this->connector->pool($iterator, $concurrency, $responseHandler, $exceptionHandler);
    }

    /**
     * Get the total number of pages
     */
    abstract protected function getTotalPages(Response $response): int;
}
