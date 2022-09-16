<?php

namespace Nati\BuilderGenerator\Property;

/**
 * @codeCoverageIgnore
 */
final class PropertyBuildStrategyCollection implements PropertyBuildStrategyResolver
{
    private array $strategies;

    public function __construct(PropertyBuildStrategy ...$strategies)
    {
        $this->strategies = $strategies;
    }

    public function shortnames(): array
    {
        return array_map(fn(PropertyBuildStrategy $strategy) => $strategy->getShortName(), $this->strategies);
    }

    public function resolve(string $strategyShortName): PropertyBuildStrategy
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->getShortName() === $strategyShortName) {
                return $strategy;
            }
        }

        throw new \InvalidArgumentException('Strategy ' . $strategyShortName . ' not found');
    }
}
