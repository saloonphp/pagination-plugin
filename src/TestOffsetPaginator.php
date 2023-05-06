<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination;

use Saloon\Contracts\Response;
use Sammyjo20\SaloonPagination\Paginators\OffsetPaginator;

class TestOffsetPaginator extends OffsetPaginator
{
    /**
     * Check if we are on the last page
     */
    protected function isLastPage(Response $response): bool
    {
        return (int)$response->json('total') === $this->getOffset();
    }

    /**
     * Get the results from the page
     */
    protected function getPageItems(Response $response): array
    {
        return $response->json('data') ?? [];
    }
}
