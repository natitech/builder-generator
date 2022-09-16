<?php

namespace Nati\BuilderGenerator\Test\Unit\Property\BuildStrategy;

use Nati\BuilderGenerator\Property\BuildStrategy\ConstructorPropertyBuildStrategy;
use Nati\BuilderGenerator\Test\Unit\UnitTest;

class ConstructorPropertyBuildStrategyTest extends UnitTest
{
    private ConstructorPropertyBuildStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->strategy = new ConstructorPropertyBuildStrategy();
    }

    /**
     * @test
     */
    public function canBuildPublicPropertyBuildBodyWithConstructorOrder()
    {
        $prop1                   = $this->makeProperty();
        $prop1->constructorOrder = 1;
        $prop2                   = $this->makeProperty('prop2');
        $prop2->constructorOrder = 0;
        $class                   = $this->makeFullClass([$prop1, $prop2]);

        $class->nbConstructorArgs = 2;

        $this->assertEquals(
            'return new TestPublic($this->prop2, $this->prop1);',
            $this->strategy->getBuildFunctionBody($class)
        );
    }

    /**
     * @test
     */
    public function whenConstructorArgsDoNotMatchPropertyCountThenThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $class                    = $this->makeFullClass([$this->makeProperty()]);
        $class->nbConstructorArgs = 2;

        $this->strategy->getBuildFunctionBody($class);
    }
}
