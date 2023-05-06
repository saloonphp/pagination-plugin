<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination;

use Saloon\Contracts\Response;
use Sammyjo20\SaloonPagination\Paginators\PagedPaginator;

class TestPagedPaginator extends PagedPaginator
{
    protected function isLastPage(Response $response): bool
    {
        return empty($response->json('next_page_url'));
    }

    /**
     * Get the results from the page
     */
    protected function getPageItems(Response $response): array
    {
        return $response->json('data') ?? [];
    }
}
