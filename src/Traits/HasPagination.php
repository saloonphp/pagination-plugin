<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Traits;

use Saloon\Contracts\Request;
use Saloon\Helpers\Helpers;
use Sammyjo20\SaloonPagination\Paginators\Paginator;

trait HasPagination
{
    public function paginate(Request $request): Paginator
    {
        if (in_array(HasPagination::class, Helpers::classUsesRecursive($request), true)) {
            return $request->paginate($request);
        }

        return $this->resolvePaginator($request);
    }

    abstract protected function resolvePaginator(Request $request): Paginator;
}
