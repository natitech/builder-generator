<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

use Faker\Generator;

final class TestNonFluentSetterBuilder
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

    public function build(): TestNonFluentSetter
    {
        $built = new TestNonFluentSetter();
        $built->setTest($this->test);
        $built->setTest2($this->test2);
        $built->setTest3($this->test3);

        return $built;
    }
}
