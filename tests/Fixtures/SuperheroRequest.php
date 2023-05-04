<?php

declare(strict_types=1);

namespace Sammyjo20\Package\Tests\Fixtures;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SuperheroRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return '/superheroes/per-page';
    }
}
