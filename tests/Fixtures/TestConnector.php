<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Tests\Fixtures;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;
use Sammyjo20\SaloonPagination\Traits\HasPagination;

abstract class TestConnector extends Connector
{
    use AlwaysThrowOnErrors;
    use HasPagination;

    /**
     * Define the base URL of the API.
     */
    public function resolveBaseUrl(): string
    {
        return 'https://tests.saloon.dev/api';
    }
}
