<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Saloon\PaginationPlugin\Tests\Fixtures\Requests\UserRequest;
use Saloon\PaginationPlugin\Tests\Fixtures\Connectors\PagedConnector;
use Saloon\PaginationPlugin\Tests\Fixtures\Connectors\CursorConnector;
use Saloon\PaginationPlugin\Tests\Fixtures\Connectors\OffsetConnector;
use Saloon\PaginationPlugin\Tests\Fixtures\Requests\MappedPagedRequest;
use Saloon\PaginationPlugin\Tests\Fixtures\Requests\SuperheroPagedRequest;
use Saloon\PaginationPlugin\Tests\Fixtures\Requests\SuperheroCursorRequest;
use Saloon\PaginationPlugin\Tests\Fixtures\Requests\SuperheroLimitOffsetRequest;

test('you can paginate automatically through many pages of results with paged pagination', function () {
    $connector = new PagedConnector;
    $request = new SuperheroPagedRequest;
    $paginator = $connector->paginate($request);

    $superheroes = [];
    $iteratorCounter = 0;

    foreach ($paginator as $item) {
        $iteratorCounter++;
        $superheroes = array_merge($superheroes, $item->json('data'));
    }

    expect($iteratorCounter)->toBe(4);
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
    $connector = new OffsetConnector;
    $request = new SuperheroLimitOffsetRequest();
    $paginator = $connector->paginate($request);

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
    $connector = new CursorConnector;
    $request = new SuperheroCursorRequest;
    $paginator = $connector->paginate($request);

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

test('you can specify the maximum number of pages to iterate over', function () {
    $connector = new CursorConnector;
    $request = new SuperheroCursorRequest();
    $paginator = $connector->paginate($request);

    $paginator->setMaxPages(2);

    $superheroes = [];
    $iteratorCounter = 0;

    foreach ($paginator as $item) {
        $iteratorCounter++;
        $superheroes = array_merge($superheroes, $item->json('data'));
    }

    expect($iteratorCounter)->toEqual(2);
    expect($superheroes)->toHaveCount(10);

    $mapped = array_map(static fn (array $superhero) => $superhero['id'], $superheroes);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
});

test('if the paginator returns all the pages in the first page it wont continue', function () {
    $connector = new PagedConnector;
    $request = new SuperheroPagedRequest();
    $paginator = $connector->paginate($request);

    $superheroes = [];
    $iteratorCounter = 0;

    $paginator->setPerPageLimit(100);

    foreach ($paginator as $response) {
        $iteratorCounter++;
        $superheroes = array_merge($superheroes, $response->json('data'));
    }

    expect($iteratorCounter)->toEqual(1);
    expect($paginator->getTotalResults())->toEqual(20);

    $mapped = array_map(static fn (array $superhero) => $superhero['id'], $superheroes);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);
});

test('the paginator will throw an exception if you use a request that is not paginatable', function () {
    $connector = new PagedConnector;
    $request = new UserRequest;

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The request must implement the `Saloon\PaginationPlugin\Contracts\Paginatable` interface to be used on paginators.');

    $connector->paginate($request);
});

test('an individual request can implement the GetPaginatedResults interface to overwrite the connector\'s paginator', function () {
    $connector = new PagedConnector;
    $request = new MappedPagedRequest;
    $paginator = $connector->paginate($request);

    $superheroes = [];

    foreach ($paginator->items() as $item) {
        $superheroes[] = $item;
    }

    expect($superheroes)->toEqual([
        'Batman',
        'Superman',
        'Flash',
        'Green Lantern',
        'Green Arrow',
        'Wonder Woman',
        'Martian Manhunter',
        'Robin/Nightwing',
        'Blue Beetle',
        'Black Canary',
        'Spider Man',
        'Captain America',
        'Iron Man',
        'Thor',
        'Hulk',
        'Wolverine',
        'Daredevil',
        'Hawkeye',
        'Cyclops',
        'Silver Surfer',
    ]);
});
