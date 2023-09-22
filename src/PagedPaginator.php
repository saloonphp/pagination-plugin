<?php

declare(strict_types=1);

namespace Saloon\PaginationPlugin;

use LogicException;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\PaginationPlugin\Traits\HasAsyncPagination;

abstract class PagedPaginator extends Paginator
{
    use HasAsyncPagination;

    /**
     * Apply pagination to the request
     */
    protected function applyPagination(Request $request): Request
    {
        $request->query()->add('page', $this->page);

        if (isset($this->perPageLimit)) {
            $request->query()->add('per_page', $this->perPageLimit);
        }

        return $request;
    }

    /**
     * Get the total number of pages
     */
    protected function getTotalPages(Response $response): int
    {
        throw new LogicException('Please implement the `getTotalPages` method on this paginator when using asynchronous pagination.');
    }
}
