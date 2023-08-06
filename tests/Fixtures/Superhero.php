<?php

declare(strict_types=1);

namespace Sammyjo20\SaloonPagination\Tests\Fixtures;

class Superhero
{
    public function __construct(
        public int $id,
        public string $superhero,
        public string $publisher,
        public string $alter_ego,
        public string $first_appearance,
        public string $characters,
    ) {
        //
    }
}
