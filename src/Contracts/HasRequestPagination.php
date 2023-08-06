<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Contracts;

use Saloon\Contracts\Connector;
use Sammyjo20\SaloonPagination\Paginators\Paginator;

interface HasRequestPagination
{
    /**
     * Paginate
     */
    public function paginate(Connector $connector): Paginator;
}
