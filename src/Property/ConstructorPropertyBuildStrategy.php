<?php

namespace Nati\BuilderGenerator\Property;

use Nati\BuilderGenerator\Analyzer\BuildableClass;

final class ConstructorPropertyBuildStrategy implements PropertyBuildStrategy
{
    public function getBuildFunctionBody(BuildableClass $class): string
    {
        $this->guardUnusableConstructor($class);

        $buildBody = 'return new ' . $class->name . '(';

        $buildBody .= implode(', ', $this->prepend($this->orderByConstructorOrder($class->properties), '$this->'));

        $buildBody .= ');';

        return $buildBody;
    }

    private function orderByConstructorOrder(array $properties): array
    {
        usort(
            $properties,
            static function ($a, $b) {
                return strcmp($a->constructorOrder, $b->constructorOrder);
            }
        );

        return $properties;
    }

    private function prepend(array $properties, string $prefix): array
    {
        $prepends = [];
        foreach ($properties as $property) {
            $prepends[] = $prefix . $property->name;
        }

        return $prepends;
    }

    private function guardUnusableConstructor(BuildableClass $class)
    {
        if (count($class->properties) !== $class->nbConstructorArgs) {
            throw new \InvalidArgumentException();
        }
    }
}
