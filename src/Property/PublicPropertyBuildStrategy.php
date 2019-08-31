<?php

namespace Nati\BuilderGenerator\Property;

use Nati\BuilderGenerator\Analyzer\BuildableClass;

final class PublicPropertyBuildStrategy implements PropertyBuildStrategy
{
    public function getBuildFunctionBody(BuildableClass $class): string
    {
        $buildBody = '$built = new ' . $class->name . '();';

        foreach ($class->properties as $property) {
            $buildBody .= "\n" . sprintf('$built->%s = $this->%s;', $property->name, $property->name);
        }

        $buildBody .= "\n\n" . 'return $built;';

        return $buildBody;
    }
}
