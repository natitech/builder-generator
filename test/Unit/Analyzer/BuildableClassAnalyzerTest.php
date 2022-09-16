<?php

namespace Nati\BuilderGenerator\Test\Unit\Analyzer;

use Nati\BuilderGenerator\Analyzer\BuildableClass;
use Nati\BuilderGenerator\Analyzer\BuildableClassAnalyzer;
use Nati\BuilderGenerator\Test\Unit\UnitTest;

class BuildableClassAnalyzerTest extends UnitTest
{
    private BuildableClassAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->analyzer = new BuildableClassAnalyzer($this->logger());
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

        $this->assertWriteStrategyIs('public', $buildableClass);
    }

    /**
     * @test
     */
    public function whenPrivatePropertyWithSetterThenWriteStrategyContainsSetter()
    {
        $buildableClass = $this->analyzer->analyse(
            '<?php namespace MyNs\Test; class MyClass{private $prop1; public function setProp1($prop1) { $this->prop1 = $prop1; }}'
        );

        $this->assertWriteStrategyIs('setter', $buildableClass);
    }

    /**
     * @test
     */
    public function whenPrivatePropertyWithFluentSetterThenWriteStrategyContainsFluentSetter()
    {
        $buildableClass = $this->analyzer->analyse(
            '<?php namespace MyNs\Test; class MyClass{private $prop1; public function setProp1($prop1) { $this->prop1 = $prop1; return $this; }}'
        );

        $this->assertWriteStrategyIs('fluent_setter', $buildableClass);
    }

    /**
     * @test
     */
    public function whenPrivatePropertyWithConstructorThenWriteStrategyContainsConstructor()
    {
        $buildableClass = $this->analyzer->analyse(
            '<?php namespace MyNs\Test; class MyClass{private $prop1; public function setProp1($prop1) { $this->prop1 = $prop1; } public function __construct($prop1) { $this->prop1 = $prop1; }}'
        );

        $expextedStrategy = 'constructor';
        $this->assertWriteStrategyIs($expextedStrategy, $buildableClass);
        $this->assertSame(0, $buildableClass->properties[0]->constructorOrder);
        $this->assertEquals(1, $buildableClass->nbConstructorArgs);
    }

    /**
     * @test
     */
    public function whenNoStrategyDefaultToStaticBuildMethod()
    {
        $buildableClass = $this->analyzer->analyse(
            '<?php namespace MyNs\Test; class MyClass{private $prop1;}'
        );

        $this->assertWriteStrategyIs('build_method', $buildableClass);
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
                 * @var integer
                 */
                public $prop2;
                
                public float $prop3;
            }'
        );

        $this->assertCount(3, $buildableClass->properties);
        $this->assertEquals('float', $buildableClass->properties[0]->inferredType);
        $this->assertEquals('int', $buildableClass->properties[1]->inferredType);
        $this->assertEquals('$faker->randomNumber()', $buildableClass->properties[1]->inferredFake);
        $this->assertEquals('float', $buildableClass->properties[2]->inferredType);
    }

    /**
     * @test
     */
    public function canInferNullableType()
    {
        $buildableClass = $this->analyzer->analyse(
            '<?php namespace MyNs\Test; 
            class MyClass{
                /**
                 * @var string|null
                 * @ORM\Column(type="string", name="test", length=255, nullable=true)
                 */
                public $address;
                
                /**
                 * @ORM\Column
                 */
                public $country;
                
                #[ORM\Column(type: "string")]
                public $zip;
                
                #[ORM\Column]
                public $city;
            }'
        );

        $this->assertCount(4, $buildableClass->properties);
        foreach ($buildableClass->properties as $property) {
            $this->assertEquals('string', $property->inferredType, $property->name . ' not a string');
        }
    }

    private function assertWriteStrategyIs(string $expextedStrategy, BuildableClass $buildableClass): void
    {
        $this->assertCount(1, $buildableClass->properties);
        $this->assertContains($expextedStrategy, $buildableClass->properties[0]->writeStrategies);
    }
}
