<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

use Faker\Generator;

final class TestFluentSetterBuilder
{
    /** @var string */
    private $email;

    /** @var string */
    private $firstName;

    public function __construct(Generator $faker)
    {
        $this->email = $faker->email;
        $this->firstName = $faker->word;
    }

    public function build(): TestFluentSetter
    {
        return (new TestFluentSetter())
        ->setEmail($this->email)
        ->setFirstName($this->firstName);
    }
}
