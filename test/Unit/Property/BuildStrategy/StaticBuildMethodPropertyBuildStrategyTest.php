<?php

namespace Nati\BuilderGenerator\Test\Unit\Property\BuildStrategy;

use Nati\BuilderGenerator\Property\BuildStrategy\StaticBuildMethodPropertyBuildStrategy;
use Nati\BuilderGenerator\Test\Unit\UnitTest;

final class StaticBuildMethodPropertyBuildStrategyTest extends UnitTest
{
    /**
     * @test
     */
    public function canBuildStaticMethodBuildBody()
    {
        $this->assertEquals(
            'return TestPublic::build(
    $this->prop1,
);',
            (new StaticBuildMethodPropertyBuildStrategy())
                ->getBuildFunctionBody($this->makeFullClass([$this->makeProperty()]))
        );
    }
}
