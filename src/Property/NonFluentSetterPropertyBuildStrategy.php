<?php

namespace Nati\BuilderGenerator\Property;

final class NonFluentSetterPropertyBuildStrategy implements PropertyBuildStrategy
{
    public function getBuildFunctionBody(string $builtClassName, array $properties): string
    {
        $buildBody = '$built = new ' . $builtClassName . '();';

        foreach ($properties as $property) {
            $buildBody .= "\n" . sprintf('$built->set%s($this->%s);', ucfirst($property), $property);
        }

        $buildBody .= "\n\n" . 'return $built;';

        return $buildBody;
    }
}
