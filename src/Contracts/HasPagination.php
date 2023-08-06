<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Contracts;

use Saloon\Contracts\Request;
use Sammyjo20\SaloonPagination\Paginators\Paginator;

interface HasPagination
{
    /**
     * Paginate
     */
    public function paginate(Request $request): Paginator;
}
