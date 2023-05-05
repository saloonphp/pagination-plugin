<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Sammyjo20\Package\TestPagedPaginator;
use Sammyjo20\Package\TestCursorPaginator;
use Sammyjo20\Package\TestOffsetPaginator;
use Sammyjo20\Package\Tests\Fixtures\TestConnector;
use Sammyjo20\Package\Tests\Fixtures\SuperheroPagedRequest;
use Sammyjo20\Package\Tests\Fixtures\SuperheroCursorRequest;
use Sammyjo20\Package\Tests\Fixtures\SuperheroLimitOffsetRequest;

test('you can paginate automatically through many pages of results with paged pagination', function () {
    $connector = new TestConnector();
    $request = new SuperheroPagedRequest();
    $paginator = new TestPagedPaginator($connector, $request);

    $superheroes = [];

    foreach ($paginator as $item) {
        $superheroes = array_merge($superheroes, $item->json('data'));
    }

    expect($paginator->getTotalResults())->toEqual(20);

    $mapped = array_map(static fn (array $superhero) => $superhero['id'], $superheroes);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);

    // Now we'll test the collect method

    $collection = $paginator->collect()->collect();

    expect($collection)->toBeInstanceOf(Collection::class)
        ->and($collection->pluck('id')->toArray())->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);

    expect($paginator->getTotalResults())->toEqual(20);
});

test('you can paginate automatically through many pages of results with limit-offset pagination', function () {
    $connector = new TestConnector();
    $request = new SuperheroLimitOffsetRequest();
    $paginator = new TestOffsetPaginator($connector, $request, 5);

    $superheroes = [];

    foreach ($paginator as $item) {
        $superheroes = array_merge($superheroes, $item->json('data'));
    }

    expect($paginator->getTotalResults())->toEqual(20);

    $mapped = array_map(static fn (array $superhero) => $superhero['id'], $superheroes);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);

    // Now we'll test the collect method

    $collection = $paginator->collect()->collect();

    expect($collection)->toBeInstanceOf(Collection::class)
        ->and($collection->pluck('id')->toArray())->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);

    expect($paginator->getTotalResults())->toEqual(20);
});

test('you can paginate automatically through many pages of results with cursor pagination', function () {
    $connector = new TestConnector();
    $request = new SuperheroCursorRequest();
    $paginator = new TestCursorPaginator($connector, $request);

    $superheroes = [];

    foreach ($paginator as $item) {
        $superheroes = array_merge($superheroes, $item->json('data'));
    }

    expect($paginator->getTotalResults())->toEqual(20);

    $mapped = array_map(static fn (array $superhero) => $superhero['id'], $superheroes);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);

    // Now we'll test the collect method

    $collection = $paginator->collect()->collect();

    expect($collection)->toBeInstanceOf(Collection::class)
        ->and($collection->pluck('id')->toArray())->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);

    expect($paginator->getTotalResults())->toEqual(20);
});
