<?php

declare(strict_types=1);

namespace Sammyjo20\Package\Paginators;

use Saloon\Contracts\Request;
use Saloon\Contracts\Response;

abstract class CursorPaginator extends Paginator
{
    /**
     * Apply the pagination to the request
     *
     * @param Request $request
     * @return Request
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
     *
     * @param Response $response
     * @return int|string
     */
    abstract protected function getNextCursor(Response $response): int|string;
}
