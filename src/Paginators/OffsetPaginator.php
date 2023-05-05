<?php

declare(strict_types=1);

namespace Sammyjo20\Package\Paginators;

use Saloon\Contracts\Request;
use Saloon\Contracts\Connector;

abstract class OffsetPaginator extends PagedPaginator
{
    /**
     * The per-page limit
     *
     * @var int
     */
    protected int $perPageLimit;

    /**
     * Constructor
     *
     * @param Connector $connector
     * @param Request $request
     * @param int $perPageLimit
     */
    public function __construct(Connector $connector, Request $request, int $perPageLimit)
    {
        parent::__construct($connector, $request);

        $this->perPageLimit = $perPageLimit;
    }

    /**
     * Apply the offset pagination
     *
     * @param Request $request
     * @return Request
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
     *
     * @return int
     */
    protected function getOffset(): int
    {
        return ($this->page - 1) * $this->perPageLimit;
    }
}
