<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Paginators;

use Saloon\Contracts\Request;
use Saloon\Contracts\Connector;

abstract class OffsetPaginator extends PagedPaginator
{
    /**
     * Constructor
     */
    public function __construct(Connector $connector, Request $request, int $perPageLimit)
    {
        parent::__construct($connector, $request);

        $this->perPageLimit = $perPageLimit;
    }

    /**
     * Apply the offset pagination
     */
    protected function applyPagination(Request $request): Request
    {
        $request->query()->merge([
            'limit' => $this->perPageLimit,
            'offset' => $this->getOffset(),
        ]);

        return $request;
    }

    /**
     * Get the offset for the paginator
     */
    protected function getOffset(): int
    {
        return ($this->page - 1) * $this->perPageLimit;
    }
}
