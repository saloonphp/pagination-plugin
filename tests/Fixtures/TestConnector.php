<?php

declare(strict_types=1);

namespace Sammyjo20\Package\Tests\Fixtures;

use Saloon\Http\Connector;
use Saloon\Contracts\Request;
use Sammyjo20\Package\TestPaginator;
use Sammyjo20\Package\PagedPaginator;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

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
