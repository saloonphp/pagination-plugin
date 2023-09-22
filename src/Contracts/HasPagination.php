<?php

declare(strict_types=1);

namespace Saloon\PaginationPlugin\Contracts;

use Saloon\Contracts\Request;
use Saloon\PaginationPlugin\Paginator;

interface HasPagination
{
    /**
     * Paginate
     */
    public function paginate(Request $request): Paginator;
}
