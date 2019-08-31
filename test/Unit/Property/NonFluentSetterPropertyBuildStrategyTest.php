<?php

namespace Nati\BuilderGenerator\Test\Unit\Property;

use Nati\BuilderGenerator\Property\NonFluentSetterPropertyBuildStrategy;
use Nati\BuilderGenerator\Test\Unit\UnitTest;

class NonFluentSetterPropertyBuildStrategyTest extends UnitTest
{
    /**
     * @test
     */
    public function canBuildPublicPropertyBuildBody()
    {
        $this->assertEquals(
            '$built = new TestPublic();
$built->setProp1($this->prop1);

return $built;',
            (new NonFluentSetterPropertyBuildStrategy())
                ->getBuildFunctionBody($this->makeClass([$this->makeProperty()]))
        );
    }
}
