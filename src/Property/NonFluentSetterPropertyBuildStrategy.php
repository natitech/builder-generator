<?php

namespace Nati\BuilderGenerator\Property;

use Nati\BuilderGenerator\Analyzer\BuildableClass;

final class NonFluentSetterPropertyBuildStrategy implements PropertyBuildStrategy
{
    public function getBuildFunctionBody(BuildableClass $class): string
    {
        $buildBody = '$built = new ' . $class->name . '();';

        foreach ($class->properties as $property) {
            $buildBody .= "\n" . sprintf('$built->set%s($this->%s);', ucfirst($property->name), $property->name);
        }

        $buildBody .= "\n\n" . 'return $built;';

        return $buildBody;
    }
}
