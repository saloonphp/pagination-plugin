<?php

namespace Sammyjo20\Package;

use Saloon\Contracts\Response;

class TestPaginator extends PagedPaginator
{
    protected function isLastPage(Response $response): bool
    {
        return empty($response->json('next_page_url'));
    }
}
