<?php

namespace Nati\BuilderGenerator\Test\Unit;

use Nati\BuilderGenerator\Analyzer\BuildableClass;
use Nati\BuilderGenerator\Analyzer\BuildableProperty;
use Nati\BuilderGenerator\Test\Double\Property\CommentPropertyBuildStrategy;
use Nati\BuilderGenerator\Test\Double\Property\NullPropertyBuildStrategy;
use PHPUnit\Framework\TestCase;

abstract class UnitTest extends TestCase
{
    protected function makeClass(array $properties): BuildableClass
    {
        $class             = new BuildableClass();
        $class->namespace  = null;
        $class->name       = 'TestPublic';
        $class->properties = $properties;

        return $class;
    }

    protected function makeFullClass(array $properties): BuildableClass
    {
        $class             = new BuildableClass();
        $class->namespace  = 'Nati\BuilderGenerator\Test\Fixtures';
        $class->name       = 'TestPublic';
        $class->properties = $properties;

        return $class;
    }

    protected function makeProperty($name = 'prop1', array $writeStrategies = []): BuildableProperty
    {
        $property                  = new BuildableProperty();
        $property->name            = $name;
        $property->inferredType    = 'string';
        $property->inferredFake    = 'word';
        $property->writeStrategies = $writeStrategies ?: $this->nullStrategies();

        return $property;
    }

    protected function mixedStrategies(): array
    {
        return array_merge($this->nullStrategies(), $this->commentStrategies());
    }

    protected function nullStrategies(): array
    {
        return [NullPropertyBuildStrategy::class];
    }

    protected function commentStrategies(): array
    {
        return [CommentPropertyBuildStrategy::class];
    }
}
