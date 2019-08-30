<?php

namespace Nati\BuilderGenerator\Property;

final class ConstructorPropertyBuildStrategy implements PropertyBuildStrategy
{
    public function getBuildFunctionBody(string $builtClassName, array $properties): string
    {
        $buildBody = 'return new ' . $builtClassName . '(';

        $buildBody .= implode(',', $this->prepend($properties, '$this->'));

        $buildBody .= ');';

        return $buildBody;
    }

    private function prepend(array $properties, string $prefix)
    {
        $prepends = [];
        foreach ($properties as $property) {
            $prepends[] = $prefix . $property;
        }

        return $prepends;
    }
}
