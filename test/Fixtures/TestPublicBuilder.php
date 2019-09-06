<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

use Faker\Generator;

final class TestPublicBuilder
{
    private $test;

    /** @var int */
    private $test4;

    /** @var float */
    private $test2;

    public function __construct(Generator $faker)
    {
        $this->test = null;
        $this->test4 = $faker->randomNumber();
        $this->test2 = $faker->randomFloat();
    }

    public function build(): TestPublic
    {
        $built = new TestPublic();
        $built->test = $this->test;
        $built->test4 = $this->test4;
        $built->test2 = $this->test2;

        return $built;
    }
}
