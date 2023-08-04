<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Tests\Fixtures;

use Saloon\Contracts\Response;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class SuperheroPagedRequest extends Request
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
     * @param \Saloon\Contracts\Response $response
     * @return array<\Sammyjo20\SaloonPagination\Tests\Fixtures\Superhero>
     */
    public function createDtoFromResponse(Response $response): array
    {
        return array_map(static function (array $superhero) {
            return new Superhero(
                $superhero['id'],
                $superhero['superhero'],
                $superhero['publisher'],
                $superhero['alter_ego'],
                $superhero['first_appearance'],
                $superhero['characters'],
            );
        }, $response->json('data'));
    }
}
