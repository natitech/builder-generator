<?php

namespace Nati\BuilderGenerator\Test\Unit\Property;

use Nati\BuilderGenerator\Property\ConstructorPropertyBuildStrategy;
use PHPUnit\Framework\TestCase;

class ConstructorPropertyBuildStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function canBuildPublicPropertyBuildBody()
    {
        $this->assertEquals(
            'return new MyClass($this->myProperty);',
            (new ConstructorPropertyBuildStrategy())->getBuildFunctionBody('MyClass', ['myProperty'])
        );
    }
}
