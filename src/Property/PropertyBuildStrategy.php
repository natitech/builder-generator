<?php

namespace Nati\BuilderGenerator\Property;

interface PropertyBuildStrategy
{
    public function getBuildFunctionBody(string $builtClassName, array $properties): string;
}
