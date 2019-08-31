<?php

namespace Nati\BuilderGenerator\Analyzer;

final class BuildableClass
{
    /** @var string */
    public $name;

    /** @var string */
    public $namespace;

    /** @var int */
    public $nbConstructorArgs;

    /** @var BuildableProperty[] */
    public $properties = [];
}
