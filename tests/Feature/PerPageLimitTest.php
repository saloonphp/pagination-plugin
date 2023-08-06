<?php

declare(strict_types=1);

use Saloon\PaginationPlugin\Tests\Fixtures\Connectors\PagedConnector;
use Saloon\PaginationPlugin\Tests\Fixtures\Connectors\CursorConnector;
use Saloon\PaginationPlugin\Tests\Fixtures\Connectors\OffsetConnector;
use Saloon\PaginationPlugin\Tests\Fixtures\Requests\SuperheroPagedRequest;
use Saloon\PaginationPlugin\Tests\Fixtures\Requests\SuperheroCursorRequest;
use Saloon\PaginationPlugin\Tests\Fixtures\Requests\SuperheroLimitOffsetRequest;

test('you can specify a perPageLimit on a paged paginator', function () {
    $connector = new PagedConnector;
    $request = new SuperheroPagedRequest();
    $paginator = $connector->paginate($request);

    $superheroes = [];
    $iteratorCounter = 0;

    $paginator->setPerPageLimit(10);

    foreach ($paginator as $item) {
        $iteratorCounter++;
        $superheroes = array_merge($superheroes, $item->json('data'));
    }

    expect($iteratorCounter)->toBe(2);
    expect($paginator->getTotalResults())->toEqual(20);

    $mapped = array_map(static fn (array $superhero) => $superhero['id'], $superheroes);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);
});

test('you can specify a perPageLimit on a limit-offset paginator', function () {
    $connector = new OffsetConnector;
    $request = new SuperheroLimitOffsetRequest();
    $paginator = $connector->paginate($request);

    $paginator->setPerPageLimit(10);

    $superheroes = [];
    $iteratorCounter = 0;

    foreach ($paginator as $item) {
        $iteratorCounter++;
        $superheroes = array_merge($superheroes, $item->json('data'));
    }

    expect($iteratorCounter)->toBe(2);
    expect($paginator->getTotalResults())->toEqual(20);

    $mapped = array_map(static fn (array $superhero) => $superhero['id'], $superheroes);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);
});

test('you can specify a perPageLimit on a cursor paginator', function () {
    $connector = new CursorConnector;
    $request = new SuperheroCursorRequest();
    $paginator = $connector->paginate($request);

    $paginator->setPerPageLimit(10);

    $superheroes = [];
    $iteratorCounter = 0;

    foreach ($paginator as $item) {
        $iteratorCounter++;
        $superheroes = array_merge($superheroes, $item->json('data'));
    }

    expect($iteratorCounter)->toBe(2);
    expect($paginator->getTotalResults())->toEqual(20);

    $mapped = array_map(static fn (array $superhero) => $superhero['id'], $superheroes);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);
});
