<?php

declare(strict_types=1);

namespace Sammyjo20\Package;

use \Iterator;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Contracts\Connector;

abstract class PagedPaginator implements Iterator
{
    protected int $page = 1;

    protected ?Response $currentResponse = null;

    public function __construct(protected Connector $connector, protected Request $request)
    {
        //
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return TValue Can return any type.
     */
    public function current(): mixed
    {
        $request = clone $this->request;

        $request->query()->add('page', $this->page);
        // Todo: Per_page

        return $this->currentResponse = $this->connector->send($request);
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next(): void
    {
        $this->page++;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return TKey|null TKey on success, or null on failure.
     */
    public function key(): mixed
    {
        return $this->page;
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        if (is_null($this->currentResponse)) {
            return true;
        }

        return $this->isLastPage($this->currentResponse) === false;
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
        $this->page = 1;
    }

    abstract protected function isLastPage(Response $response): bool;
}
