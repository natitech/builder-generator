<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

use Faker\Generator;

final class TestPublicBuilder
{
    private $test;

    private $test2;

    private $test3;

    public function __construct(Generator $faker)
    {
        $this->test = $faker->word;
        $this->test2 = $faker->word;
        $this->test3 = $faker->word;
    }

    public function build(): TestPublic
    {
        $built = new TestPublic();
        $built->test = $this->test;
        $built->test2 = $this->test2;
        $built->test3 = $this->test3;

        return $built;
    }
}
