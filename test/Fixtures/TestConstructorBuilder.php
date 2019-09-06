<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

use Faker\Generator;

final class TestConstructorBuilder
{
    /** @var \DateTime */
    private $test;

    /** @var string */
    private $city;

    public function __construct(Generator $faker)
    {
        $this->test = $faker->dateTime;
        $this->city = $faker->city;
    }

    public function build(): TestConstructor
    {
        return new TestConstructor($this->city, $this->test);
    }
}
