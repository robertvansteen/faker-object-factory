<?php

namespace Tests\Stubs;

class DummyClassWithCustomObject
{
    public function __construct(
        public Person $person,
    ) {
    }
}
