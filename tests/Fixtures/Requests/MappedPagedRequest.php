<?php

declare(strict_types=1);

namespace Saloon\PaginationPlugin\Tests\Fixtures\Requests;

use Saloon\Contracts\Response;
use Saloon\Enums\Method;
use Saloon\Contracts\Request as RequestContract;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\MapPaginatedResponseItems;
use Saloon\PaginationPlugin\Contracts\Paginatable;

class MappedPagedRequest extends Request implements Paginatable, MapPaginatedResponseItems
{
    protected Method $method = Method::GET;

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return '/superheroes/per-page';
    }

    /**
     * Map the items from the paginator
     */
    public function mapPaginatedResponseItems(Response $response): array
    {
        return $response->collect('data')->pluck('superhero')->toArray();
    }
}
