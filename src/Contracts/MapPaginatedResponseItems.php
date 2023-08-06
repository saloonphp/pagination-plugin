<?php

declare(strict_types=1);

namespace Saloon\PaginationPlugin\Contracts;

use Saloon\Contracts\Response;

interface MapPaginatedResponseItems
{
    /**
     * Map the items from the paginator
     */
    public function mapPaginatedResponseItems(Response $response): array;
}
