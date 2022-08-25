<?php

namespace Nati\BuilderGenerator\Property;

interface PropertyBuildStrategyResolver
{
    /**
     * @throws \InvalidArgumentException when $strategyClass can not be created
     */
    public function resolveStrategy(string $strategyClass): PropertyBuildStrategy;
}
