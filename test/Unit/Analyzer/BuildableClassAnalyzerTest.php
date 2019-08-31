<?php

namespace Nati\BuilderGenerator\Test\Unit\Analyzer;

use Nati\BuilderGenerator\Analyzer\BuildableClassAnalyzer;
use Nati\BuilderGenerator\Property\ConstructorPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\FluentSetterPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\NonFluentSetterPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\PublicPropertyBuildStrategy;
use Nati\BuilderGenerator\Test\Unit\UnitTest;

class BuildableClassAnalyzerTest extends UnitTest
{
    /** @var BuildableClassAnalyzer */
    private $analyzer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->analyzer = new BuildableClassAnalyzer();
    }

    /**
     * @test
     */
    public function whenContentIsNotPHPThenFQNThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->analyzer->analyse('<?php this is not php code.');
    }

    /**
     * @test
     */
    public function whenContentIsNotAClassThenFQNThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->analyzer->analyse('<?php $a = $a + 1;');
    }

    /**
     * @test
     */
    public function canReturnNameAndNamespace()
    {
        $buildableClass = $this->analyzer->analyse('<?php namespace MyNs\Test; class MyClass{}');

        $this->assertEquals('MyClass', $buildableClass->name);
        $this->assertEquals('MyNs\Test', $buildableClass->namespace);
    }

    /**
     * @test
     */
    public function canAddPropertiesNames()
    {
        $buildableClass = $this->analyzer->analyse(
            '<?php namespace MyNs\Test; class MyClass{private $prop1; private $prop2;}'
        );

        $this->assertCount(2, $buildableClass->properties);
        $this->assertEquals('prop1', $buildableClass->properties[0]->name);
        $this->assertEquals('prop2', $buildableClass->properties[1]->name);
    }

    /**
     * @test
     */
    public function whenPublicPropertyThenWriteStrategyIsPublic()
    {
        $buildableClass = $this->analyzer->analyse(
            '<?php namespace MyNs\Test; class MyClass{public $prop1;}'
        );

        $this->assertCount(1, $buildableClass->properties);
        $this->assertEquals([PublicPropertyBuildStrategy::class], $buildableClass->properties[0]->writeStrategies);
    }

    /**
     * @test
     */
    public function whenPrivatePropertyWithSetterThenWriteStrategyContainsSetter()
    {
        $buildableClass = $this->analyzer->analyse(
            '<?php namespace MyNs\Test; class MyClass{private $prop1; public function setProp1($prop1) { $this->prop1 = $prop1; }}'
        );

        $this->assertCount(1, $buildableClass->properties);
        $this->assertContains(
            NonFluentSetterPropertyBuildStrategy::class,
            $buildableClass->properties[0]->writeStrategies
        );
    }

    /**
     * @test
     */
    public function whenPrivatePropertyWithFluentSetterThenWriteStrategyContainsFluentSetter()
    {
        $buildableClass = $this->analyzer->analyse(
            '<?php namespace MyNs\Test; class MyClass{private $prop1; public function setProp1($prop1) { $this->prop1 = $prop1; return $this; }}'
        );

        $this->assertCount(1, $buildableClass->properties);
        $this->assertContains(
            FluentSetterPropertyBuildStrategy::class,
            $buildableClass->properties[0]->writeStrategies
        );
    }

    /**
     * @test
     */
    public function whenPrivatePropertyWithConstructorThenWriteStrategyContainsConstructor()
    {
        $buildableClass = $this->analyzer->analyse(
            '<?php namespace MyNs\Test; class MyClass{private $prop1; public function setProp1($prop1) { $this->prop1 = $prop1; } public function __construct($prop1) { $this->prop1 = $prop1; }}'
        );

        $this->assertCount(1, $buildableClass->properties);
        $this->assertContains(
            ConstructorPropertyBuildStrategy::class,
            $buildableClass->properties[0]->writeStrategies
        );
        $this->assertSame(0, $buildableClass->properties[0]->constructorOrder);
        $this->assertEquals(1, $buildableClass->nbConstructorArgs);
    }

    /**
     * @test
     */
    public function canInferScalarTypes()
    {
        $buildableClass = $this->analyzer->analyse(
            '<?php namespace MyNs\Test; 
            class MyClass{
                /** @var float */
                public $prop1;
                
                /** 
                 * Prop 2
                 * @var int 
                 */
                public $prop2;
            }'
        );

        $this->assertCount(2, $buildableClass->properties);
        $this->assertEquals('float', $buildableClass->properties[0]->inferredType);
        $this->assertEquals('int', $buildableClass->properties[1]->inferredType);
    }
}
