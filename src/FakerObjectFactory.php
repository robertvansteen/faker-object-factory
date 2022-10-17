<?php

namespace Rovansteen\FakerObjectFactory;

use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use Rovansteen\FakerObjectFactory\Exceptions\MissingGeneratorException;

class FakerObjectFactory
{
    protected Generator $faker;

    /**
     * @var array<ObjectFactoryGenerator>
     */
    protected array $generators = [];

    /**
     * @param Generator|null $faker
     * @param array<ObjectFactoryGenerator> $generators
     */
    public function __construct(?Generator $faker = null, array $generators = [])
    {
        $this->faker = $faker ?? Factory::create();
        $this->generators = $generators;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param array<string, mixed> $arguments
     * @return T
     * @throws ReflectionException
     * @throws MissingGeneratorException
     */
    public function create(string $className, array $arguments = [])
    {
        $reflection = new ReflectionClass($className);

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $parameters = $constructor->getParameters();

        $arguments = $this->resolveArguments($parameters, $arguments);

        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * @param array<ReflectionParameter> $parameters
     * @param array<string, mixed> $arguments
     * @return array<int, mixed>
     * @throws MissingGeneratorException
     */
    private function resolveArguments(array $parameters, array $arguments): array
    {
        $resolvedArguments = [];

        foreach ($parameters as $parameter) {
            $resolvedArguments[] = $this->resolveArgument($parameter, $arguments);
        }

        return $resolvedArguments;
    }

    /**
     * @param ReflectionParameter $parameter
     * @param array<string, mixed> $arguments
     * @return mixed
     * @throws MissingGeneratorException
     */
    private function resolveArgument(ReflectionParameter $parameter, array $arguments): mixed
    {
        $name = $parameter->getName();

        if (array_key_exists($name, $arguments)) {
            return $arguments[$name];
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->allowsNull()) {
            return null;
        }

        if (!$type = $parameter->getType()) {
            throw new InvalidArgumentException("Parameter {$name} has no type hint");
        }

        if ($type instanceof ReflectionIntersectionType) {
            throw new InvalidArgumentException("Intersection types can not be generated.");
        }

        if ($type instanceof ReflectionNamedType) {
            $value = $this->generateValue($type);
            if (!is_null($value)) {
                return $value;
            }
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $type) {
                $value = $this->generateValue($type);
                if (!is_null($value)) {
                    return $value;
                }
            }
        }

        throw new MissingGeneratorException(sprintf(
            "Could not generate value for: [%s] of type [%s]",
            $parameter->name,
            $type->getName()
        ));
    }

    protected function generateValue(ReflectionNamedType $type): mixed
    {
        if ($type->isBuiltin()) {
            return $this->generateBuiltInValue($type);
        }

        foreach ($this->generators as $generator) {
            /** @var ObjectFactoryGenerator $generator */
            /** @var class-string $class */
            $class = $type->getName();
            if ($generator->supports($class)) {
                return $generator->setFaker($this->faker)->generate($type->getName());
            }
        }

        return null;
    }

    protected function generateBuiltInValue(ReflectionNamedType $type): mixed
    {
        return match ($type->getName()) {
            'string' => $this->faker->word,
            'int' => $this->faker->numberBetween(),
            'array' => [],
            'float' => $this->faker->randomFloat(),
            'object' => new \stdClass(),
            default => null,
        };
    }

    public function addGenerator(ObjectFactoryGenerator $generator): void
    {
        $this->generators[] = $generator;
    }
}
