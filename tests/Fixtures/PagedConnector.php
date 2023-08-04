<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Tests\Fixtures;

use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Sammyjo20\SaloonPagination\Contracts\HasPagination;
use Sammyjo20\SaloonPagination\Contracts\HasRequestPagination;
use Sammyjo20\SaloonPagination\Paginators\CursorPaginator;
use Sammyjo20\SaloonPagination\Paginators\PagedPaginator;
use Sammyjo20\SaloonPagination\Paginators\Paginator;
use Sammyjo20\SaloonPagination\Traits\HasAsyncPagination;

class PagedConnector extends TestConnector implements HasPagination
{
    public function paginate(Request $request): CursorPaginator
    {
        if ($request instanceof HasRequestPagination) {
            return $request->paginate($this);
        }

        return new class(connector: $this, request: $request) extends CursorPaginator {
            use HasAsyncPagination;

            protected function getNextCursor(Response $response): int|string
            {
                // TODO: Implement getNextCursor() method.
            }

            protected function isLastPage(Response $response): bool
            {
                // TODO: Implement isLastPage() method.
            }

            protected function getPageItems(Response $response): array
            {
                // TODO: Implement getPageItems() method.
            }

            protected function getTotalPages(Response $response): int
            {
                // TODO: Implement getTotalPages() method.
            }
        };

        return new PagedPaginator(
            connector: $this,
            request: $request,
            isLastPage: static function (Response $response, PagedPaginator $paginator): bool {
                return $paginator->getPage() - 1 === $response->json('total') / 5;

                // return empty($response->json('next_page_url'));
            },
            getPageItems: static function (Response $response): array {
                return $response->json('data') ?? [];
            },
            // Only provide if you want async ⬇️
//            getTotalPages: static function (Response $response): int {
//                return $response->json('to');
//            }
        );
    }
}
