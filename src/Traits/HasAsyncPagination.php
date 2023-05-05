<?php

declare(strict_types=1);

namespace Sammyjo20\Package\Traits;

use Saloon\Http\Pool;
use Saloon\Contracts\Response;

trait HasAsyncPagination
{
    /**
     * Determines if async is enabled or not
     *
     * @var bool
     */
    protected bool $async = false;

    /**
     * Check if async is enabled or not
     *
     * @param bool $async
     * @return $this
     */
    public function async(bool $async = true): static
    {
        $this->async = $async;

        return $this;
    }

    /**
     * Create an async pool for the iterator
     *
     * @param callable|int $concurrency
     * @param callable|null $responseHandler
     * @param callable|null $exceptionHandler
     * @return Pool
     */
    public function pool(callable|int $concurrency = 5, ?callable $responseHandler = null, ?callable $exceptionHandler = null): Pool
    {
        $this->async();

        return $this->connector->pool($this, $concurrency, $responseHandler, $exceptionHandler);
    }

    /**
     * Get the total number of pages
     *
     * @param Response $response
     * @return int
     */
    abstract protected function getTotalPages(Response $response): int;
}
