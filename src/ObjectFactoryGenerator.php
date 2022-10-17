<?php

declare(strict_types=1);

namespace Rovansteen\FakerObjectFactory;

use Faker\Generator;

abstract class ObjectFactoryGenerator
{
    protected Generator $faker;

    /**
     * @param class-string $className
     */
    abstract public function supports(string $className): bool;

    abstract public function generate(string $className): mixed;

    public function setFaker(Generator $faker): self
    {
        $this->faker = $faker;

        return $this;
    }
}
