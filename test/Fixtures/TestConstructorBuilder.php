<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

use Faker\Generator;

final class TestConstructorBuilder
{
    /** @var string */
    private $test;

    /** @var string */
    private $test2;

    public function __construct(Generator $faker)
    {
        $this->test = $faker->word;
        $this->test2 = $faker->word;
    }

    public function build(): TestConstructor
    {
        return new TestConstructor($this->test2, $this->test);
    }
}
