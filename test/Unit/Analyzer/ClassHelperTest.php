<?php

namespace Nati\BuilderGenerator\Test\Unit\Analyzer;

use Nati\BuilderGenerator\Analyzer\ClassHelper;
use Nati\BuilderGenerator\Property\ConstructorPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\NonFluentSetterPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\PublicPropertyBuildStrategy;
use Nati\BuilderGenerator\Test\Fixtures\TestConstructor;
use Nati\BuilderGenerator\Test\Fixtures\TestPublic;
use Nati\BuilderGenerator\Test\Fixtures\TestNonFluentSetter;
use PHPUnit\Framework\TestCase;

class ClassHelperTest extends TestCase
{
    /** @var \Nati\BuilderGenerator\Analyzer\ClassHelper */
    private $classHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classHelper = new ClassHelper();
    }

    /**
     * @test
     */
    public function whenContentIsNotPHPThenFQNThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->classHelper->getFQN('this is not php code.');
    }

    /**
     * @test
     */
    public function whenContentIsNotAClassThenFQNThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->classHelper->getFQN('<?php $a = $a + 1;');
    }

    /**
     * @test
     */
    public function whenNoNamespaceThenReturnClass()
    {
        $this->assertEquals('MyClass', $this->classHelper->getFQN('<?php class MyClass{}'));
    }

    /**
     * @test
     */
    public function canReturnFQN()
    {
        $this->assertEquals(
            'MyNs\Test\MyClass',
            $this->classHelper->getFQN('<?php namespace MyNs\Test; class MyClass{}')
        );
    }

    /**
     * @test
     */
    public function whenMostlyPublicPropertiesThenBuiltStrategyIsPublic()
    {
        $this->assertInstanceOf(
            PublicPropertyBuildStrategy::class,
            $this->classHelper->getPropertyBuildStrategy(TestPublic::class)
        );
    }

    /**
     * @test
     */
    public function whenMostlyPrivatePropertiesWithoutConstructorThenBuiltStrategyIsNonFluentSetter()
    {
        $this->assertInstanceOf(
            NonFluentSetterPropertyBuildStrategy::class,
            $this->classHelper->getPropertyBuildStrategy(TestNonFluentSetter::class)
        );
    }

    /**
     * @test
     * @group wip
     * Need to use a real parser for this
     */
    public function whenMostlyPrivatePropertiesWithConstructorThenBuiltStrategyIsConstructor()
    {
        $this->assertInstanceOf(
            ConstructorPropertyBuildStrategy::class,
            $this->classHelper->getPropertyBuildStrategy(TestConstructor::class)
        );
    }
}
