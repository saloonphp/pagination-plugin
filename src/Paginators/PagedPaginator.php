<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Paginators;

use Saloon\Contracts\Request;

abstract class PagedPaginator extends Paginator
{
    /**
     * Apply pagination to the request
     *
     * @param Request $request
     * @return Request
     */
    protected function applyPagination(Request $request): Request
    {
        $request->query()->add('page', $this->page);

        // Todo: Apply per-page logic

        return $request;
    }
}
