<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

final class TestConstructor
{
    public  $test3;

    /** @var string */
    private $test;

    private $city;

    private $test4;

    public function __construct(string $city, $test)
    {
        $this->test = $test;
        $this->city = $city;
    }

    public function getTest()
    {
        return $this->test;
    }

    public function getTest4()
    {
        return $this->test4;
    }

    public function setTest4($test4)
    {
        $this->test4 = $test4;

        return $this;
    }
}
