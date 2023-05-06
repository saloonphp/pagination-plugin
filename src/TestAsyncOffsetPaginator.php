<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination;

use Saloon\Contracts\Response;
use Sammyjo20\SaloonPagination\Traits\HasAsyncPagination;
use Sammyjo20\SaloonPagination\Paginators\OffsetPaginator;

class TestAsyncOffsetPaginator extends OffsetPaginator
{
    use HasAsyncPagination;

    /**
     * Get the results from the page
     */
    protected function getPageItems(Response $response): array
    {
        return $response->json('data') ?? [];
    }

    /**
     * Get the total number of pages
     */
    protected function getTotalPages(Response $response): int
    {
        return (int)ceil($response->json('total') / $this->perPageLimit);
    }
}
