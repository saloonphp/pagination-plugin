<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Paginators;

use Saloon\Contracts\Request;
use Saloon\Contracts\Response;

abstract class CursorPaginator extends Paginator
{
    /**
     * Apply the pagination to the request
     */
    protected function applyPagination(Request $request): Request
    {
        if ($this->currentResponse instanceof Response) {
            $request->query()->add('cursor', $this->getNextCursor($this->currentResponse));
        }

        return $request;
    }

    /**
     * Get the next cursor
     */
    abstract protected function getNextCursor(Response $response): int|string;
}
