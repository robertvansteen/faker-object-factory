<?php

namespace Tests\Stubs;

use Countable;
use Iterator;

class DummyClassWithIntersectionType
{
    public function __construct(
        public Iterator&Countable $value
    ) {
    }
}
