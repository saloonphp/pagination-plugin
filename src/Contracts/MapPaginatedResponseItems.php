<?php

declare(strict_types=1);

namespace Saloon\PaginationPlugin\Contracts;

use Saloon\Http\Response;

interface MapPaginatedResponseItems
{
    /**
     * Map the items from the paginator
     *
     * @return array<mixed, mixed>
     */
    public function mapPaginatedResponseItems(Response $response): array;
}
