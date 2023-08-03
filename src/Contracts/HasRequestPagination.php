<?php

namespace Sammyjo20\SaloonPagination\Contracts;

use Saloon\Contracts\Connector;
use Sammyjo20\SaloonPagination\Paginators\Paginator;

interface HasRequestPagination
{
    /**
     * Paginate
     *
     * @param \Saloon\Contracts\Connector $connector
     * @return \Sammyjo20\SaloonPagination\Paginators\Paginator
     */
    public function paginate(Connector $connector): Paginator;
}
