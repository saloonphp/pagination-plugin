<?php

declare(strict_types=1);

use GuzzleHttp\Promise\Promise;
use Illuminate\Support\Collection;
use Sammyjo20\SaloonPagination\TestAsyncPagedPaginator;
use Sammyjo20\SaloonPagination\TestAsyncOffsetPaginator;
use Sammyjo20\SaloonPagination\Tests\Fixtures\TestConnector;
use Sammyjo20\SaloonPagination\Tests\Fixtures\SuperheroPagedRequest;
use Sammyjo20\SaloonPagination\Tests\Fixtures\SuperheroLimitOffsetRequest;

test('you can paginate asynchronously through many pages of results with paged pagination', function () {
    $connector = new TestConnector();
    $request = new SuperheroPagedRequest();
    $paginator = new TestAsyncPagedPaginator($connector, $request);

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
    $connector = new TestConnector();
    $request = new SuperheroLimitOffsetRequest();
    $paginator = new TestAsyncOffsetPaginator($connector, $request, 5);

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

test('if you dont implement the isLastPage on an asynchronous paginator it will throw an exception on synchronous pagination', function () {
    $connector = new TestConnector();
    $request = new SuperheroPagedRequest();
    $paginator = new TestAsyncPagedPaginator($connector, $request);

    expect($paginator->isAsyncPaginationEnabled())->toBeFalse();

    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('Please implement the `isLastPage` method on this paginator when not using asynchronous pagination.');

    foreach ($paginator as $item) {
        //
    }
});
