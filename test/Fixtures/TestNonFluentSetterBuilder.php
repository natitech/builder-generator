<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

use Faker\Generator;

final class TestNonFluentSetterBuilder
{
    private string $lastname;
    private string $firstName;

    public function __construct(Generator $faker)
    {
        $this->lastname = $faker->lastName;
        $this->firstName = $faker->word;
    }

    public function build(): TestNonFluentSetter
    {
        $built = new TestNonFluentSetter();
        $built->setLastname($this->lastname);
        $built->setFirstName($this->firstName);

        return $built;
    }
}
