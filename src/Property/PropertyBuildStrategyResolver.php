<?php

namespace Nati\BuilderGenerator\Property;

interface PropertyBuildStrategyResolver
{
    /**
     * @param string $strategyClass
     * @return \Nati\BuilderGenerator\Property\PropertyBuildStrategy
     * @throws \InvalidArgumentException when $strategyClass can not be created
     */
    public function resolveStrategy(string $strategyClass): PropertyBuildStrategy;
}
