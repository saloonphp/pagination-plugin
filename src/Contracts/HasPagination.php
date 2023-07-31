<?php

namespace Sammyjo20\SaloonPagination\Contracts;

use Saloon\Contracts\Request;
use Sammyjo20\SaloonPagination\Paginators\Paginator;

interface HasPagination
{
    /**
     * Paginate
     *
     * @param \Saloon\Contracts\Request $request
     * @return \Sammyjo20\SaloonPagination\Paginators\Paginator
     */
    public function paginate(Request $request): Paginator;
}
