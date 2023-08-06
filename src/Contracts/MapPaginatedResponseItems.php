<?php

namespace Saloon\PaginationPlugin\Contracts;

use Saloon\Contracts\Request;
use Saloon\Contracts\Response;

interface MapPaginatedResponseItems
{
    /**
     * Map the items from the paginator
     */
    public function mapPaginatedResponseItems(Response $response): array;
}
