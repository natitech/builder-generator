<?php

namespace Nati\BuilderGenerator\Test\Double\Property;

use Nati\BuilderGenerator\Property\PropertyBuildStrategy;
use Nati\BuilderGenerator\Property\PropertyBuildStrategyResolver;

final class PropertyBuildStrategyResolverMock implements PropertyBuildStrategyResolver
{
    private PropertyBuildStrategy $strategy;

    public function __construct()
    {
        $this->strategy  = new NullPropertyBuildStrategy();
    }

    public function resolve(string $strategyShortName): PropertyBuildStrategy
    {
        return $this->strategy;
    }

    public function setStrategy(PropertyBuildStrategy $strategy): self
    {
        $this->strategy = $strategy;

        return $this;
    }
}
