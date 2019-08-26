<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

use Faker\Generator;

final class TestBuilder
{
    private $test;

    public function __construct(Generator $faker)
    {
        $this->test = $faker->word;
    }

    public function build()
    {
        $built = new Test();
        $built->test = $this->test;

        return $built;
    }
}
