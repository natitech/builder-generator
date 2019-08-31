<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

final class TestUnbuildableConstructor
{
    public  $test3;

    private $test;

    private $test2;

    private $test4;

    public function __construct($other, $test, $test2)
    {
        $this->test  = $test;
        $this->test2 = $test2;
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
