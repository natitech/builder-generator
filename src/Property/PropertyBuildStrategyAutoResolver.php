<?php

namespace Nati\BuilderGenerator\Property;

final class PropertyBuildStrategyAutoResolver implements PropertyBuildStrategyResolver
{
    private array $resolved = [];

    public function resolveStrategy(string $strategyClass): PropertyBuildStrategy
    {
        return $this->resolved[$strategyClass] ?? $this->resolved[$strategyClass] = new $strategyClass();
    }
}
