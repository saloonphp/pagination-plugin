<?php

declare(strict_types=1);

namespace Sammyjo20\Package;

use Saloon\Contracts\Response;
use Sammyjo20\Package\Traits\HasAsyncPagination;
use Sammyjo20\Package\Paginators\OffsetPaginator;

class TestAsyncOffsetPaginator extends OffsetPaginator
{
    use HasAsyncPagination;

    /**
     * Check if we are on the last page
     *
     * @param Response $response
     * @return bool
     */
    protected function isLastPage(Response $response): bool
    {
        return (int)$response->json('total') === $this->getOffset();
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
        return ($response->json('total') / count($this->getPageItems($response))) + 1;
    }
}
