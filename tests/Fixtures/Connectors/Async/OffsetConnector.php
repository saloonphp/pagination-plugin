<?php

declare(strict_types=1);

namespace Saloon\PaginationPlugin\Tests\Fixtures\Connectors\Async;

use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\PaginationPlugin\OffsetPaginator;
use Saloon\PaginationPlugin\Contracts\HasPagination;
use Saloon\PaginationPlugin\Contracts\HasRequestPagination;
use Saloon\PaginationPlugin\Tests\Fixtures\Connectors\TestConnector;

class OffsetConnector extends TestConnector implements HasPagination
{
    /**
     * Paginate over each page
     */
    public function paginate(Request $request): OffsetPaginator
    {
        if ($request instanceof HasRequestPagination) {
            return $request->paginate($this);
        }

        return new class(connector: $this, request: $request) extends OffsetPaginator {
            /**
             * Per Page
             */
            protected ?int $perPageLimit = 5;

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
            protected function getPageItems(Response $response, Request $request): array
            {
                return $response->json('data') ?? [];
            }

            /**
             * Get the total number of pages
             */
            protected function getTotalPages(Response $response): int
            {
                return $response->json('total') / $this->perPageLimit;
            }
        };
    }
}
