<?php

declare(strict_types=1);

use Saloon\Contracts\Response;
use Sammyjo20\SaloonPagination\TestAsyncPagedPaginator;
use Sammyjo20\SaloonPagination\TestAsyncOffsetPaginator;
use Sammyjo20\SaloonPagination\Tests\Fixtures\TestConnector;
use Sammyjo20\SaloonPagination\Tests\Fixtures\SuperheroPagedRequest;
use Sammyjo20\SaloonPagination\Tests\Fixtures\SuperheroLimitOffsetRequest;

test('you can make a pool of requests with paged pagination', function () {
    $connector = new TestConnector();
    $request = new SuperheroPagedRequest();
    $paginator = new TestAsyncPagedPaginator($connector, $request);

    $superheroes = [];
    $iteratorCounter = 0;

    $paginator->pool(5, function (Response $response) use (&$iteratorCounter, &$superheroes) {
        $iteratorCounter++;
        $superheroes = array_merge($superheroes, $response->json('data'));
    })->send()->wait();

    expect($iteratorCounter)->toEqual(4);
    expect($paginator->getTotalResults())->toEqual(20);

    $mapped = array_map(static fn (array $superhero) => $superhero['id'], $superheroes);

    sort($mapped);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20]);
});

test('you can make a pool of requests with limit-offset pagination', function () {
    $connector = new TestConnector();
    $request = new SuperheroLimitOffsetRequest();
    $paginator = new TestAsyncOffsetPaginator($connector, $request, 5);

    $superheroes = [];
    $iteratorCounter = 0;

    $paginator->pool(5, function (Response $response) use (&$iteratorCounter, &$superheroes) {
        $iteratorCounter++;
        $superheroes = array_merge($superheroes, $response->json('data'));
    })->send()->wait();

    expect($iteratorCounter)->toEqual(4);
    expect($paginator->getTotalResults())->toEqual(20);

    $mapped = array_map(static fn (array $superhero) => $superhero['id'], $superheroes);

    sort($mapped);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20]);
});
