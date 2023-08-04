<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Tests\Fixtures;

use Closure;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Sammyjo20\SaloonPagination\Contracts\AsyncPaginator;
use Sammyjo20\SaloonPagination\Contracts\HasPagination;
use Sammyjo20\SaloonPagination\Contracts\HasRequestPagination;
use Sammyjo20\SaloonPagination\Paginators\PagedPaginator;
use Sammyjo20\SaloonPagination\Traits\HasAsyncPagination;

class PagedConnector extends TestConnector implements HasPagination
{
    /**
     * Paginate over each page
     *
     * @param \Saloon\Contracts\Request $request
     * @return \Sammyjo20\SaloonPagination\Paginators\PagedPaginator
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
            protected function getPageItems(Response $response, Closure $useDto): array
            {
                return $useDto();
            }

            protected function getTotalPages(Response $response): int
            {
                return $response->json('total') / $response->json('per_page');
            }
        };
    }
}
