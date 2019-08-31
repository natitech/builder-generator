<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

use Faker\Generator;

final class TestPublicBuilder
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

    public function build(): TestPublic
    {
        $built = new TestPublic();
        $built->test = $this->test;
        $built->test2 = $this->test2;

        return $built;
    }
}
