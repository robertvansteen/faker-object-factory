<?php

namespace Tests\Stubs;

use Rovansteen\FakerObjectFactory\ObjectFactoryGenerator;

class PersonGenerator extends ObjectFactoryGenerator
{
    public function supports(string $className): bool
    {
        return is_a($className, Person::class, true);
    }

    public function generate(string $className): Person
    {
        return new Person(name: $this->faker->name, age: $this->faker->numberBetween(0, 100));
    }
}
