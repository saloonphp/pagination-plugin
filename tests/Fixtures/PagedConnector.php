<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Tests\Fixtures;

use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Sammyjo20\SaloonPagination\Contracts\HasPagination;
use Sammyjo20\SaloonPagination\Contracts\HasRequestPagination;
use Sammyjo20\SaloonPagination\Paginators\PagedPaginator;

class PagedConnector extends TestConnector implements HasPagination
{
    public function paginate(Request $request): PagedPaginator
    {
        if ($request instanceof HasRequestPagination) {
            return $request->paginate($this);
        }

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
