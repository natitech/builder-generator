<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

use Faker\Generator;

final class TestNonFluentSetterBuilder
{
    /** @var string */
    private $test3;

    /** @var string */
    private $test;

    /** @var string */
    private $test2;

    public function __construct(Generator $faker)
    {
        $this->test3 = $faker->word;
        $this->test = $faker->word;
        $this->test2 = $faker->word;
    }

    public function build(): TestNonFluentSetter
    {
        $built = new TestNonFluentSetter();
        $built->setTest3($this->test3);
        $built->setTest($this->test);
        $built->setTest2($this->test2);

        return $built;
    }
}
