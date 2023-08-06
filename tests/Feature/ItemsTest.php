<?php

declare(strict_types=1);

use Saloon\PaginationPlugin\Tests\Fixtures\Connectors\PagedConnector;
use Saloon\PaginationPlugin\Tests\Fixtures\Requests\SuperheroPagedRequest;

test('you can iterate through the items of a paginated resource', function () {
    $connector = new PagedConnector;
    $request = new SuperheroPagedRequest();
    $paginator = $connector->paginate($request);

    $superheroes = [];
    $iteratorCounter = 0;

    foreach ($paginator->items() as $item) {
        $superheroes[] = $item;
        $iteratorCounter++;
    }

    expect($iteratorCounter)->toBe(20);

    $mapped = array_map(static fn (array $superhero) => $superhero['id'], $superheroes);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);
});
