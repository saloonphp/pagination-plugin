<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\PaginationPlugin\Exceptions\PaginationException;
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

test('the pagination plugin can detect a potential infinite loop', function () {
    $mockClient = new MockClient([
        SuperheroPagedRequest::class => MockResponse::make(['next_page_url' => 'infinity']),
    ]);

    $connector = new PagedConnector;
    $connector->withMockClient($mockClient);

    // We'll create a paginator that expects the "next_page_url" to be empty.
    // This will compare the responses and throw an exception if the last
    // five responses have been the same.

    $paginator = $connector->paginate(new SuperheroPagedRequest);

    $thrownException = false;

    try {
        iterator_to_array($paginator);
    } catch (PaginationException $exception) {
        expect($exception->getMessage())->toEqual('Potential infinite loop detected! The last 5 requests have had exactly the same body. You can use the $detectInfiniteLoop property on your paginator to disable this check.');

        $mockClient->assertSentCount(5);

        $thrownException = true;
    }

    expect($thrownException)->toBeTrue();
});

test('the pagination plugin can detect a potential infinite loop after the initial request', function () {
    // We'll have two regular requests sent, and then we'll have 6 exactly the same
    // response bodies.

    $mockClient = new MockClient([
        MockResponse::make(['next_page_url' => '2']),
        MockResponse::make(['next_page_url' => '3']),
        MockResponse::make(['next_page_url' => 'infinity']),
        MockResponse::make(['next_page_url' => 'infinity']),
        MockResponse::make(['next_page_url' => 'infinity']),
        MockResponse::make(['next_page_url' => 'infinity']),
        MockResponse::make(['next_page_url' => 'infinity']),
        MockResponse::make(['next_page_url' => 'infinity']),
    ]);

    $connector = new PagedConnector;
    $connector->withMockClient($mockClient);

    $paginator = $connector->paginate(new SuperheroPagedRequest);

    $thrownException = false;

    try {
        iterator_to_array($paginator);
    } catch (PaginationException $exception) {
        expect($exception->getMessage())->toEqual('Potential infinite loop detected! The last 5 requests have had exactly the same body. You can use the $detectInfiniteLoop property on your paginator to disable this check.');

        $mockClient->assertSentCount(7);

        $thrownException = true;
    }

    expect($thrownException)->toBeTrue();
});

test('the paginator only keeps five response checksums in memory at once', function () {
    $mockClient = new MockClient([
        MockResponse::make($responseA = ['next_page_url' => '1']),
        MockResponse::make($responseB = ['next_page_url' => '2']),
        MockResponse::make($responseC = ['next_page_url' => '3']),
        MockResponse::make($responseD = ['next_page_url' => '4']),
        MockResponse::make($responseE = ['next_page_url' => '5']),
        MockResponse::make($responseF = ['next_page_url' => '6']),
        MockResponse::make($responseG = ['next_page_url' => null]),
    ]);

    $connector = new PagedConnector;
    $connector->withMockClient($mockClient);

    $paginator = $connector->paginate(new SuperheroPagedRequest);

    iterator_to_array($paginator);

    $previousBodyChecksums = invade($paginator)->lastFiveBodyChecksums;

    expect($previousBodyChecksums)->toBeArray();

    // We should only have four items because after each successful fifth attempt
    // we will remove the oldest one from the array.

    expect($previousBodyChecksums)->toHaveCount(4);

    expect($previousBodyChecksums[0])->toBe(md5(json_encode($responseD)));
    expect($previousBodyChecksums[1])->toBe(md5(json_encode($responseE)));
    expect($previousBodyChecksums[2])->toBe(md5(json_encode($responseF)));
    expect($previousBodyChecksums[3])->toBe(md5(json_encode($responseG)));
});
