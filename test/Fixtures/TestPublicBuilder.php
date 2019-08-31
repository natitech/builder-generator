<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

use Faker\Generator;

final class TestPublicBuilder
{
    private $test;

    /** @var float */
    private $test2;

    public function __construct(Generator $faker)
    {
        $this->test = null;
        $this->test2 = $faker->randomFloat();
    }

    public function build(): TestPublic
    {
        $built = new TestPublic();
        $built->test = $this->test;
        $built->test2 = $this->test2;

        return $built;
    }
}
