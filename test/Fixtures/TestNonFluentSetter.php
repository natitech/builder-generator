<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

final class TestNonFluentSetter
{
    public  $test3;

    private $test;

    private $test2;

    public function __construct()
    {
    }

    public function getTest()
    {
        return $this->test;
    }

    public function setTest($test)
    {
        $this->test = $test;

        return $this;
    }

    public function getTest2()
    {
        return $this->test2;
    }

    public function setTest2($test2)
    {
        $this->test2 = $test2;
    }

    public function getTest3()
    {
        return $this->test3;
    }

    public function setTest3($test3)
    {
        $this->test3 = $test3;
    }
}
