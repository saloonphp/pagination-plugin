<?php

declare(strict_types=1);

namespace Saloon\PaginationPlugin\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

class SuperheroCursorRequest extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return '/superheroes/cursor';
    }
}
