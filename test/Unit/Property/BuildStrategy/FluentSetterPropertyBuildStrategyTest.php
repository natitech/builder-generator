<?php

namespace Nati\BuilderGenerator\Test\Unit\Property\BuildStrategy;

use Nati\BuilderGenerator\Property\BuildStrategy\FluentSetterPropertyBuildStrategy;
use Nati\BuilderGenerator\Test\Unit\UnitTest;

class FluentSetterPropertyBuildStrategyTest extends UnitTest
{
    /**
     * @test
     */
    public function canBuildPublicPropertyBuildBody()
    {
        $this->assertEquals(
            'return (new TestPublic())
->setProp1($this->prop1);',
            (new FluentSetterPropertyBuildStrategy())
                ->getBuildFunctionBody($this->makeFullClass([$this->makeProperty()]))
        );
    }
}
