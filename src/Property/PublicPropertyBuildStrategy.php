<?php

namespace Nati\BuilderGenerator\Property;

use Nati\BuilderGenerator\Analyzer\BuildableClass;

final class PublicPropertyBuildStrategy implements PropertyBuildStrategy
{
    public function getBuildFunctionBody(BuildableClass $class): string
    {
        $this->guardUnusableConstructor($class);

        $buildBody = '$built = new ' . $class->name . '();';

        foreach ($class->properties as $property) {
            $buildBody .= "\n" . sprintf('$built->%s = $this->%s;', $property->name, $property->name);
        }

        $buildBody .= "\n\n" . 'return $built;';

        return $buildBody;
    }

    private function guardUnusableConstructor(BuildableClass $class)
    {
        if ($class->nbConstructorArgs > 0) {
            throw new \InvalidArgumentException('Class constructor can not be used');
        }
    }
}
