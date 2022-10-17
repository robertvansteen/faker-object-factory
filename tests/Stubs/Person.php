<?php

namespace Tests\Stubs;

class Person
{
    public function __construct(
        public string $name,
        public int $age,
    ) {
    }
}
