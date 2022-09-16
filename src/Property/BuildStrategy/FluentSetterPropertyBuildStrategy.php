<?php

namespace Nati\BuilderGenerator\Property\BuildStrategy;

use Nati\BuilderGenerator\Analyzer\BuildableClass;
use Nati\BuilderGenerator\Property\PropertyBuildStrategy;

final class FluentSetterPropertyBuildStrategy implements PropertyBuildStrategy
{
    public function getShortName(): string
    {
        return 'fluent_setter';
    }

    public function getBuildFunctionBody(BuildableClass $class): string
    {
        $this->guardUnusableConstructor($class);

        $buildBody = 'return (new ' . $class->name . '())';

        foreach ($class->properties as $property) {
            $buildBody .= "\n" . sprintf('->set%s($this->%s)', ucfirst($property->name), $property->name);
        }

        $buildBody .= ';';

        return $buildBody;
    }

    private function guardUnusableConstructor(BuildableClass $class)
    {
        if ($class->nbConstructorArgs > 0) {
            throw new \InvalidArgumentException('Class seems to be buildable by setters but has constructor dependencies');
        }
    }
}
