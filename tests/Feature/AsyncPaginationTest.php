<?php

declare(strict_types=1);

use GuzzleHttp\Promise\Promise;
use Illuminate\Support\Collection;
use Saloon\PaginationPlugin\Tests\Fixtures\Requests\SuperheroPagedRequest;
use Saloon\PaginationPlugin\Tests\Fixtures\Connectors\Async\PagedConnector;
use Saloon\PaginationPlugin\Tests\Fixtures\Connectors\Async\OffsetConnector;
use Saloon\PaginationPlugin\Tests\Fixtures\Requests\SuperheroLimitOffsetRequest;
use Saloon\PaginationPlugin\Tests\Fixtures\Connectors\PagedConnector as SyncPagedConnector;

test('you can paginate asynchronously through many pages of results with paged pagination', function () {
    $connector = new PagedConnector;
    $request = new SuperheroPagedRequest;
    $paginator = $connector->paginate($request);

    expect($paginator->isAsyncPaginationEnabled())->toBeFalse();

    $paginator->async();

    expect($paginator->isAsyncPaginationEnabled())->toBeTrue();

    $superheroes = [];

    $iteratorCounter = 0;

    foreach ($paginator as $promise) {
        $iteratorCounter++;
        $superheroes = array_merge($superheroes, $promise->wait()->json('data'));
    }

    expect($iteratorCounter)->toEqual(4);
    expect($paginator->getTotalResults())->toEqual(20);

    $mapped = array_map(static fn (array $superhero) => $superhero['id'], $superheroes);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);

    // Now we'll test the collect method

    $collection = $paginator->collect()
        ->map(fn (Promise $promise) => $promise->wait()->json('data'))
        ->collapse()
        ->collect();

    expect($collection)->toBeInstanceOf(Collection::class)
        ->and($collection->sortBy('id')->pluck('id')->toArray())->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);

    expect($paginator->getTotalResults())->toEqual(20);
});

test('you can paginate asynchronously through many pages of results with limit-offset pagination', function () {
    $connector = new OffsetConnector;
    $request = new SuperheroLimitOffsetRequest();
    $paginator = $connector->paginate($request);

    expect($paginator->isAsyncPaginationEnabled())->toBeFalse();

    $paginator->async();

    expect($paginator->isAsyncPaginationEnabled())->toBeTrue();

    $superheroes = [];

    $iteratorCounter = 0;

    foreach ($paginator as $promise) {
        $iteratorCounter++;
        $superheroes = array_merge($superheroes, $promise->wait()->json('data'));
    }

    expect($iteratorCounter)->toEqual(4);
    expect($paginator->getTotalResults())->toEqual(20);

    $mapped = array_map(static fn (array $superhero) => $superhero['id'], $superheroes);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);

    // Now we'll test the collect method

    $collection = $paginator->collect()
        ->map(fn (Promise $promise) => $promise->wait()->json('data'))
        ->collapse()
        ->collect();

    expect($collection)->toBeInstanceOf(Collection::class)
        ->and($collection->sortBy('id')->pluck('id')->toArray())->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);

    expect($paginator->getTotalResults())->toEqual(20);
});

test('if you dont implement the getTotalPages method on a paginator it will throw an exception if you try to use async pagination', function () {
    $connector = new SyncPagedConnector;
    $request = new SuperheroPagedRequest();
    $paginator = $connector->paginate($request)->async();

    expect($paginator->isAsyncPaginationEnabled())->toBeTrue();

    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('Please implement the `getTotalPages` method on this paginator when using asynchronous pagination.');

    foreach ($paginator as $item) {
        //
    }
});

test('if the paginator returns all the pages in the first page it wont continue', function () {
    $connector = new PagedConnector;
    $request = new SuperheroPagedRequest();
    $paginator = $connector->paginate($request);

    expect($paginator->isAsyncPaginationEnabled())->toBeFalse();

    $paginator->async();

    expect($paginator->isAsyncPaginationEnabled())->toBeTrue();

    $superheroes = [];

    $iteratorCounter = 0;

    $paginator->setPerPageLimit(100);

    foreach ($paginator as $promise) {
        $iteratorCounter++;
        $superheroes = array_merge($superheroes, $promise->wait()->json('data'));
    }

    expect($iteratorCounter)->toEqual(1);
    expect($paginator->getTotalResults())->toEqual(20);

    $mapped = array_map(static fn (array $superhero) => $superhero['id'], $superheroes);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);
});
