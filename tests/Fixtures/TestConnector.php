<?php

namespace Sammyjo20\Package\Tests\Fixtures;

use Saloon\Contracts\HasPagination;
use Saloon\Contracts\Paginator;
use Saloon\Contracts\Request;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;
use Sammyjo20\Package\PagedPaginator;
use Sammyjo20\Package\TestPaginator;

class TestConnector extends Connector
{
    use AlwaysThrowOnErrors;

    /**
     * Define the base URL of the API.
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return 'https://tests.saloon.dev/api';
    }

    public function paginate(Request $request): PagedPaginator
    {
        return new TestPaginator($this, $request);
    }
}
