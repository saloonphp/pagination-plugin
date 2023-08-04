<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Traits;

use LogicException;
use Saloon\Http\Pool;
use Saloon\Contracts\Response;

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
        return $this->connector->pool($this->async(), $concurrency, $responseHandler, $exceptionHandler);
    }

    /**
     * Method used to determine if the paginator is on the last page
     *
     * Note: This is not used for async pagination
     */
    protected function isLastPage(Response $response): bool
    {
        throw new LogicException('Please implement the `isLastPage` method on this paginator when not using asynchronous pagination.');
    }

    /**
     * Get the total number of pages
     */
    abstract protected function getTotalPages(Response $response): int;
}
