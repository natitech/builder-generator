<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

use Faker\Generator;

final class TestConstructorBuilder
{
    private $test;

    private $test2;

    private $test3;

    private $test4;

    public function __construct(Generator $faker)
    {
        $this->test = $faker->word;
        $this->test2 = $faker->word;
        $this->test3 = $faker->word;
        $this->test4 = $faker->word;
    }

    public function build(): TestConstructor
    {
        $built = new TestConstructor();
        $built->setTest($this->test);
        $built->setTest2($this->test2);
        $built->setTest3($this->test3);
        $built->setTest4($this->test4);

        return $built;
    }
}
