<?php

namespace Nati\BuilderGenerator\Test\Unit\Property\BuildStrategy;

use Nati\BuilderGenerator\Property\BuildStrategy\NonFluentSetterPropertyBuildStrategy;
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
                ->getBuildFunctionBody($this->makeFullClass([$this->makeProperty()]))
        );
    }
}
