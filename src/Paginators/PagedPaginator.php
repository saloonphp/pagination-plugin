<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Paginators;

use Closure;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Contracts\Connector;
use Sammyjo20\SaloonPagination\Traits\HasAsyncPagination;

class PagedPaginator extends Paginator
{
    use HasAsyncPagination;

    /**
     * Closure for the "isLastPage" method
     */
    protected Closure $isLastPage;

    /**
     * Closure for the "getPageItems" method
     */
    protected Closure $getPageItems;

    /**
     * Closure for the "getTotalPages" method
     */
    protected ?Closure $getTotalPages;

    /**
     * Constructor
     */
    public function __construct(Connector $connector, Request $request, Closure $isLastPage, Closure $getPageItems, Closure $getTotalPages = null, Closure $beforeRequest = null)
    {
        $this->isLastPage = $isLastPage;
        $this->getPageItems = $getPageItems;
        $this->getTotalPages = $getTotalPages;
        $this->setBeforeRequest($beforeRequest);

        parent::__construct($connector, $request);
    }

    /**
     * Apply pagination to the request
     */
    protected function applyPagination(Request $request): Request
    {
        $request->query()->add('page', $this->page);

        if (isset($this->perPageLimit)) {
            $request->query()->add('per_page', $this->perPageLimit);
        }

        return $request;
    }

    protected function isLastPage(Response $response): bool
    {
        return call_user_func($this->isLastPage, $response);
    }

    protected function getPageItems(Response $response): array
    {
        return call_user_func($this->getPageItems, $response);
    }

    protected function getTotalPages(Response $response): int
    {
        return call_user_func($this->getTotalPages, $response);
    }
}
