<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Tests\Fixtures;

use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Sammyjo20\SaloonPagination\Paginators\Paginator;
use Sammyjo20\SaloonPagination\Paginators\PagedPaginator;

class PagedConnector extends TestConnector
{
    protected function resolvePaginator(Request $request): Paginator
    {
        return new PagedPaginator(
            connector: $this,
            request: $request,
            isLastPage: static function (Response $response): bool {
                return empty($response->json('next_page_url'));
            },
            getPageItems: static function (Response $response): array {
                return $response->json('data') ?? [];
            },
            // Only provide if you want async ⬇️
            getTotalPages: static function (Response $response): int {
                return $response->json('total');
            }
        );
    }
}
