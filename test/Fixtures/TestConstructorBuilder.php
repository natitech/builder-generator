<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

use Faker\Generator;

final class TestConstructorBuilder
{
    private \DateTime $test;

    private string $city;

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
