<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Tests\Fixtures;

use Saloon\Contracts\Connector;
use Saloon\Contracts\Response;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Sammyjo20\SaloonPagination\Contracts\HasRequestPagination;
use Sammyjo20\SaloonPagination\Paginators\PagedPaginator;
use Sammyjo20\SaloonPagination\Paginators\Paginator;

class SuperheroPagedRequest extends Request implements HasRequestPagination
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

    /**
     * Paginate
     *
     * @param \Saloon\Contracts\Connector $connector
     * @return \Sammyjo20\SaloonPagination\Paginators\Paginator
     */
    public function paginate(Connector $connector): Paginator
    {
        return new class (connector: $connector, request: $this) extends PagedPaginator {
            /**
             * Check if we are on the last page
             */
            protected function isLastPage(Response $response): bool
            {
                return true;

                // TODO: Implement isLastPage() method.
            }

            /**
             * Get the results from the page
             */
            protected function getPageItems(Response $response, \Saloon\Contracts\Request $request): array
            {
                return $request->createDtoFromResponse($response);

                // TODO: Implement getPageItems() method.
            }
        };
    }
}
