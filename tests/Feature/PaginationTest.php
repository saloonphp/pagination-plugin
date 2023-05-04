<?php

use Sammyjo20\Package\PagedPaginator;
use Sammyjo20\Package\Tests\Fixtures\SuperheroRequest;
use Sammyjo20\Package\Tests\Fixtures\TestConnector;

test('you can paginate automatically through many pages of results', function () {
    $connector = new TestConnector();
    $paginator = $connector->paginate(new SuperheroRequest());

    $superheroes = [];

    foreach ($paginator as $item) {
        $superheroes = array_merge($superheroes, $item->json('data'));
    }

    $mapped = array_map(static fn(array $superhero) => $superhero['id'], $superheroes);

    expect($mapped)->toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,]);
});

test('you can paginate asynchronously through many pages of results', function () {
    //
});
