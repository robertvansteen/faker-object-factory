<?php

namespace Tests\Stubs;

class DummyClassWithUnionType
{
    public function __construct(
        public string|int $stringOrInt,
    ) {
    }
}
