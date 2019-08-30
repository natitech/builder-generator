<?php

namespace Nati\BuilderGenerator\Test\Double\Property;

use Nati\BuilderGenerator\Property\PropertyBuildStrategy;

final class PropertyBuildStrategyStub implements PropertyBuildStrategy
{
    public function getBuildFunctionBody(string $builtClassName, array $properties): string
    {
        return 'body';
    }
}
