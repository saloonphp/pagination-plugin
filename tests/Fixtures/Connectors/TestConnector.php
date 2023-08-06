<?php

declare(strict_types=1);

namespace Saloon\PaginationPlugin\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

abstract class TestConnector extends Connector
{
    use AlwaysThrowOnErrors;

    /**
     * Define the base URL of the API.
     */
    public function resolveBaseUrl(): string
    {
        return 'https://tests.saloon.dev/api';
    }
}
