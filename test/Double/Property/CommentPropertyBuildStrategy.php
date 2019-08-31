<?php

namespace Nati\BuilderGenerator\Test\Double\Property;

use Nati\BuilderGenerator\Analyzer\BuildableClass;
use Nati\BuilderGenerator\Property\PropertyBuildStrategy;

final class CommentPropertyBuildStrategy implements PropertyBuildStrategy
{
    public function getBuildFunctionBody(BuildableClass $class): string
    {
        return sprintf('//CommentPropertyBuildStrategy with %d properties', count($class->properties));
    }
}
