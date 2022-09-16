<?php

namespace Nati\BuilderGenerator\Test\Unit\Property\BuildStrategy;

use Nati\BuilderGenerator\Property\BuildStrategy\PublicPropertyBuildStrategy;
use Nati\BuilderGenerator\Test\Unit\UnitTest;

class PublicPropertyBuildStrategyTest extends UnitTest
{
    /**
     * @test
     */
    public function canBuildPublicPropertyBuildBody()
    {
        $this->assertEquals(
            '$built = new TestPublic();
$built->prop1 = $this->prop1;

return $built;',
            (new PublicPropertyBuildStrategy())->getBuildFunctionBody($this->makeFullClass([$this->makeProperty()]))
        );
    }
}
