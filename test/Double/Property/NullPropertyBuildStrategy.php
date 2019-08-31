<?php

namespace Nati\BuilderGenerator\Test\Double\Property;

use Nati\BuilderGenerator\Analyzer\BuildableClass;
use Nati\BuilderGenerator\Property\PropertyBuildStrategy;

final class NullPropertyBuildStrategy implements PropertyBuildStrategy
{
    public function getBuildFunctionBody(BuildableClass $class): string
    {
        return 'return null;';
    }
}
