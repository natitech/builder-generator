<?php

namespace Nati\BuilderGenerator\Property;

use Nati\BuilderGenerator\Analyzer\BuildableClass;

interface PropertyBuildStrategy
{
    public function getBuildFunctionBody(BuildableClass $class): string;
}
