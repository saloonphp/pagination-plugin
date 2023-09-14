<?php

declare(strict_types=1);

namespace Saloon\PaginationPlugin\Contracts;

use Saloon\Http\Connector;
use Saloon\PaginationPlugin\Paginator;

interface HasRequestPagination
{
    /**
     * Paginate
     */
    public function paginate(Connector $connector): Paginator;
}
