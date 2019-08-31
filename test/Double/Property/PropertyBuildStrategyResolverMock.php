<?php

namespace Nati\BuilderGenerator\Test\Double\Property;

use Nati\BuilderGenerator\Property\PropertyBuildStrategy;
use Nati\BuilderGenerator\Property\PropertyBuildStrategyResolver;

final class PropertyBuildStrategyResolverMock implements PropertyBuildStrategyResolver
{
    public function resolveStrategy(string $strategyClass): PropertyBuildStrategy
    {
        return new $strategyClass();
    }
}
