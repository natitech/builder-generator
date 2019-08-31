<?php

namespace Nati\BuilderGenerator\Property;

final class PropertyBuildStrategyAutoResolver implements PropertyBuildStrategyResolver
{
    private $resolved = [];

    public function resolveStrategy(string $strategyClass): PropertyBuildStrategy
    {
        return $this->resolved[$strategyClass] ?? $this->resolved[$strategyClass] = new $strategyClass();
    }
}
