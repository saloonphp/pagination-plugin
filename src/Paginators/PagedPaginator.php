<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Paginators;

use Saloon\Contracts\Request;

abstract class PagedPaginator extends Paginator
{
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
}
