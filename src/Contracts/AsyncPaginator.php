<?php

namespace Sammyjo20\SaloonPagination\Contracts;

use Saloon\Http\Pool;

interface AsyncPaginator
{
    /**
     * Enable or disable async pagination
     *
     * @param bool $async
     * @return $this
     */
    public function async(bool $async = true): static;

    /**
     * Create an async pool for the iterator
     *
     * @param callable|int $concurrency
     * @param callable|null $responseHandler
     * @param callable|null $exceptionHandler
     * @return \Saloon\Http\Pool
     */
    public function pool(callable|int $concurrency = 5, ?callable $responseHandler = null, ?callable $exceptionHandler = null): Pool;
}
