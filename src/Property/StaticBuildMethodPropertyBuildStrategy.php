<?php

namespace Nati\BuilderGenerator\Property;

use Nati\BuilderGenerator\Analyzer\BuildableClass;

final class StaticBuildMethodPropertyBuildStrategy implements PropertyBuildStrategy
{
    public function getBuildFunctionBody(BuildableClass $class): string
    {
        $this->guardUnusableConstructor($class);

        $buildBody = 'return ' . $class->name . '::build(';

        foreach ($class->properties as $property) {
            $buildBody .= "\n" . sprintf('    $this->%s,', $property->name);
        }

        $buildBody .= "\n" . ');';

        return $buildBody;
    }

    private function guardUnusableConstructor(BuildableClass $class)
    {
        if ($class->nbConstructorArgs > 0) {
            throw new \InvalidArgumentException('Class seems to be buildable by static build method but has constructor dependencies');
        }
    }
}
