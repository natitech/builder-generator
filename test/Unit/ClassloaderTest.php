<?php

namespace Nati\BuilderGenerator\Test\Unit;

use Nati\BuilderGenerator\Classloader;
use PHPUnit\Framework\TestCase;

class ClassloaderTest extends TestCase
{
    /** @var \Nati\BuilderGenerator\Classloader */
    private $classloader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classloader = new Classloader();
    }

    /**
     * @test
     */
    public function whenContentIsNotPHPThenFQNThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->classloader->getFQN('this is not php code.');
    }

    /**
     * @test
     */
    public function whenContentIsNotAClassThenFQNThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->classloader->getFQN('<?php $a = $a + 1;');
    }

    /**
     * @test
     */
    public function whenNoNamespaceThenReturnClass()
    {
        $this->assertEquals('MyClass', $this->classloader->getFQN('<?php class MyClass{}'));
    }

    /**
     * @test
     */
    public function canReturnFQN()
    {
        $this->assertEquals(
            'MyNs\Test\MyClass',
            $this->classloader->getFQN('<?php namespace MyNs\Test; class MyClass{}')
        );
    }
}
