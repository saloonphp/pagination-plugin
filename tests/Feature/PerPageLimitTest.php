<?php

declare(strict_types=1);

use Sammyjo20\SaloonPagination\TestPagedPaginator;
use Sammyjo20\SaloonPagination\TestCursorPaginator;
use Sammyjo20\SaloonPagination\TestOffsetPaginator;
use Sammyjo20\SaloonPagination\Tests\Fixtures\TestConnector;
use Sammyjo20\SaloonPagination\Tests\Fixtures\SuperheroPagedRequest;
use Sammyjo20\SaloonPagination\Tests\Fixtures\SuperheroCursorRequest;
use Sammyjo20\SaloonPagination\Tests\Fixtures\SuperheroLimitOffsetRequest;

test('you can specify a perPageLimit on a paged paginator', function () {
    $connector = new TestConnector();
    $request = new SuperheroPagedRequest();
    $paginator = new TestPagedPaginator($connector, $request);

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
    $connector = new TestConnector();
    $request = new SuperheroLimitOffsetRequest();
    $paginator = new TestOffsetPaginator($connector, $request, 5);

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
    $connector = new TestConnector();
    $request = new SuperheroCursorRequest();
    $paginator = new TestCursorPaginator($connector, $request);

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
