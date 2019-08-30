<?php

namespace Nati\BuilderGenerator\Test\Unit\Property;

use Nati\BuilderGenerator\Property\PublicPropertyBuildStrategy;
use PHPUnit\Framework\TestCase;

class PublicPropertyBuildStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function canBuildPublicPropertyBuildBody()
    {
        $this->assertEquals(
            '$built = new MyClass();
$built->myProperty = $this->myProperty;

return $built;',
            (new PublicPropertyBuildStrategy())->getBuildFunctionBody('MyClass', ['myProperty'])
        );
    }
}
