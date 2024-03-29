<?php

declare(strict_types=1);

use Saloon\Http\Response;
use Illuminate\Support\Collection;
use Saloon\PaginationPlugin\Tests\Fixtures\Connectors\PagedConnector;
use Saloon\PaginationPlugin\Tests\Fixtures\Requests\SuperheroPagedRequest;

test('can collect through paginated responses and not items', function () {
    $connector = new PagedConnector;
    $request = new SuperheroPagedRequest();
    $paginator = $connector->paginate($request);

    $collection = $paginator->collect(throughItems: false)->collect();

    expect($collection)->toBeInstanceOf(Collection::class);
    expect($collection)->each->toBeInstanceOf(Response::class);

    function toIds(array $items): array
    {
        return array_map(static fn (array $items) => $items['id'], $items);
    }

    expect(toIds($collection[0]->json('data')))->toEqual([1, 2, 3, 4, 5]);
    expect(toIds($collection[1]->json('data')))->toEqual([6, 7, 8, 9, 10]);
    expect(toIds($collection[2]->json('data')))->toEqual([11, 12, 13, 14, 15]);
    expect(toIds($collection[3]->json('data')))->toEqual([16, 17, 18, 19, 20]);

    expect($paginator->getTotalResults())->toEqual(20);
});
