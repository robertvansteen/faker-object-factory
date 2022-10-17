<?php

namespace Tests\Stubs;

class DummyClassWithScalars
{
    public function __construct(
        public string $string,
        public int $int,
        public float $float,
        /** @var array<array-key, mixed> $array */
        public array $array,
        public object $object,
    ) {
    }
}
