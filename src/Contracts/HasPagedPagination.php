<?php

namespace Sammyjo20\SaloonPagination\Contracts;

use Saloon\Contracts\Request;
use Sammyjo20\SaloonPagination\Paginators\PagedPaginator;

interface HasPagedPagination extends HasPagination
{
    /**
     * Paginate
     *
     * @param \Saloon\Contracts\Request $request
     * @return \Sammyjo20\SaloonPagination\Paginators\PagedPaginator
     */
    public function paginate(Request $request): PagedPaginator;
}
