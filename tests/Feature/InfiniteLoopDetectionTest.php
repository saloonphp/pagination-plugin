<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Exceptions\NoMockResponseFoundException;
use Saloon\PaginationPlugin\Exceptions\PaginationException;
use Saloon\PaginationPlugin\Tests\Fixtures\Connectors\PagedConnector;
use Saloon\PaginationPlugin\Tests\Fixtures\Requests\SuperheroPagedRequest;
use Saloon\PaginationPlugin\Tests\Fixtures\Connectors\DisabledInfiniteLoopConnector;

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

test('you can disable the infinite loop detection on a paginator', function () {
    $mockClient = new MockClient([
        MockResponse::make(['next_page_url' => 'infinity']),
        MockResponse::make(['next_page_url' => 'infinity']),
        MockResponse::make(['next_page_url' => 'infinity']),
        MockResponse::make(['next_page_url' => 'infinity']),
        MockResponse::make(['next_page_url' => 'infinity']),
        MockResponse::make(['next_page_url' => 'infinity']),
    ]);

    $connector = new DisabledInfiniteLoopConnector;
    $connector->withMockClient($mockClient);

    $paginator = $connector->paginate(new SuperheroPagedRequest);

    $thrownException = false;

    try {
        iterator_to_array($paginator);
    } catch (NoMockResponseFoundException) {
        // We'll detect a "NoMockResponseFoundException" because our mock client should attempt to
        // keep making requests because the infinite loop detection has been disabled.

        $thrownException = true;
    }

    expect($thrownException)->toBeTrue();
});
