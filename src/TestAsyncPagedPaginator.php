<?php

declare(strict_types=1);

namespace Sammyjo20\Package;

use Saloon\Contracts\Response;
use Sammyjo20\Package\Paginators\PagedPaginator;
use Sammyjo20\Package\Traits\HasAsyncPagination;

class TestAsyncPagedPaginator extends PagedPaginator
{
    use HasAsyncPagination;

    protected function isLastPage(Response $response): bool
    {
        return empty($response->json('next_page_url'));
    }

    /**
     * Get the results from the page
     *
     * @param Response $response
     * @return array
     */
    protected function getPageItems(Response $response): array
    {
        return $response->json('data') ?? [];
    }

    /**
     * Get the total number of pages
     *
     * @param Response $response
     * @return int
     */
    protected function getTotalPages(Response $response): int
    {
        return $response->json('to');
    }
}
