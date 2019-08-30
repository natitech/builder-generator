<?php

namespace Nati\BuilderGenerator\Test\Unit\Property;

use Nati\BuilderGenerator\Property\NonFluentSetterPropertyBuildStrategy;
use PHPUnit\Framework\TestCase;

class NonFluentSetterPropertyBuildStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function canBuildPublicPropertyBuildBody()
    {
        $this->assertEquals(
            '$built = new MyClass();
$built->setMyProperty($this->myProperty);

return $built;',
            (new NonFluentSetterPropertyBuildStrategy())->getBuildFunctionBody('MyClass', ['myProperty'])
        );
    }
}
