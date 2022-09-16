<?php

namespace Nati\BuilderGenerator\Property;

interface PropertyBuildStrategyResolver
{
    /**
     * @throws \InvalidArgumentException when $strategyShortName is not found
     */
    public function resolve(string $strategyShortName): PropertyBuildStrategy;
}
