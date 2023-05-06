<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination;

use Saloon\Contracts\Response;
use Sammyjo20\SaloonPagination\Paginators\PagedPaginator;
use Sammyjo20\SaloonPagination\Traits\HasAsyncPagination;

class TestAsyncPagedPaginator extends PagedPaginator
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
        return (int)ceil($response->json('total') / $response->json('per_page'));
    }
}
