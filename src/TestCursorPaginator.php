<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination;

use Saloon\Contracts\Response;
use Sammyjo20\SaloonPagination\Paginators\CursorPaginator;

class TestCursorPaginator extends CursorPaginator
{
    /**
     * Get the next cursor
     *
     * @param Response $response
     * @return int|string
     */
    protected function getNextCursor(Response $response): int|string
    {
        $nextPageUrl = $response->json('next_page_url');
        parse_str(parse_url($nextPageUrl, PHP_URL_QUERY), $queryParams);

        return $queryParams['cursor'];
    }

    /**
     * Check if we are on the last page
     *
     * @param Response $response
     * @return bool
     */
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
        return $response->json('data');
    }
}
