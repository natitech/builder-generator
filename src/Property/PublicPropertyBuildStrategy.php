<?php

namespace Nati\BuilderGenerator\Property;

final class PublicPropertyBuildStrategy implements PropertyBuildStrategy
{
    public function getBuildFunctionBody(string $builtClassName, array $properties): string
    {
        $buildBody = '$built = new ' . $builtClassName . '();';

        foreach ($properties as $property) {
            $buildBody .= "\n" . sprintf('$built->%s = $this->%s;', $property, $property);
        }

        $buildBody .= "\n\n" . 'return $built;';

        return $buildBody;
    }
}
