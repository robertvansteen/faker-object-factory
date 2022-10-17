<?php

namespace Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Rovansteen\FakerObjectFactory\Exceptions\MissingGeneratorException;
use Rovansteen\FakerObjectFactory\FakerObjectFactory;
use Tests\Stubs\DummyClassWithCustomObject;
use Tests\Stubs\DummyClassWithIntersectionType;
use Tests\Stubs\DummyClassWithNullableScalars;
use Tests\Stubs\DummyClassWithScalars;
use Tests\Stubs\DummyClassWithUnionType;
use Tests\Stubs\Person;
use Tests\Stubs\PersonGenerator;

class FakerObjectFactoryTest extends TestCase
{
    /** @test */
    public function it_generates_fake_values_for_scalars(): void
    {
        $faker = new FakerObjectFactory();
        $object = $faker->create(DummyClassWithScalars::class, []);
        $this->assertIsString($object->string);
        $this->assertIsArray($object->array);
        $this->assertIsObject($object->object);
        $this->assertIsFloat($object->float);
        $this->assertIsInt($object->int);
    }

    /** @test */
    public function it_sets_null_when_allowed(): void
    {
        $faker = new FakerObjectFactory();
        $object = $faker->create(DummyClassWithNullableScalars::class, []);
        $this->assertNull($object->string);
        $this->assertNull($object->array);
        $this->assertNull($object->object);
        $this->assertNull($object->float);
        $this->assertNull($object->int);
    }

    /** @test */
    public function it_generates_fake_value_for_union_type(): void
    {
        $faker = new FakerObjectFactory();
        $object = $faker->create(DummyClassWithUnionType::class, []);
        $this->assertThat($object->stringOrInt, $this->logicalOr($this->isType('string'), $this->isType('int')));
    }

    /** @test */
    public function it_throws_error_for_intersection_types(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $faker = new FakerObjectFactory();
        $faker->create(DummyClassWithIntersectionType::class, []);
    }

    /** @test */
    public function it_allows_overriding_arguments_with_custom_input(): void
    {
        $faker = new FakerObjectFactory();
        $object = $faker->create(DummyClassWithScalars::class, ['string' => 'foo']);
        $this->assertEquals('foo', $object->string);
    }

    /** @test */
    public function it_throws_if_it_cant_generate_value_for_non_built_in_types(): void
    {
        $this->expectException(MissingGeneratorException::class);
        $faker = new FakerObjectFactory();
        $faker->create(DummyClassWithCustomObject::class);
    }

    /** @test */
    public function it_generates_a_value_for_a_custom_object_through_generator(): void
    {
        $faker = new FakerObjectFactory();
        $faker->addGenerator(new PersonGenerator());
        $object = $faker->create(DummyClassWithCustomObject::class);
        $this->assertInstanceOf(Person::class, $object->person);
    }
}
