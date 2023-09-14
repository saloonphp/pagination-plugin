<?php

declare(strict_types=1);

namespace Saloon\PaginationPlugin\Tests\Fixtures\Connectors;

use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\PagedPaginator;
use Saloon\PaginationPlugin\Contracts\HasPagination;
use Saloon\PaginationPlugin\Contracts\HasRequestPagination;

class PagedConnector extends TestConnector implements HasPagination
{
    /**
     * Paginate over each page
     */
    public function paginate(Request $request): PagedPaginator
    {
        if ($request instanceof HasRequestPagination) {
            return $request->paginate($this);
        }

        return new class(connector: $this, request: $request) extends PagedPaginator {
            /**
             * Check if we are on the last page
             */
            protected function isLastPage(Response $response): bool
            {
                return empty($response->json('next_page_url'));
            }

            /**
             * Get the results from the page
             */
            protected function getPageItems(Response $response, Request $request): array
            {
                return $response->json('data') ?? [];
            }
        };
    }
}
