<?php

namespace Nati\BuilderGenerator\Analyzer;

final class BuildableClass
{
    public string $name;

    public ?string $namespace;

    public int $nbConstructorArgs = 0;

    /** @var BuildableProperty[] */
    public array $properties = [];
}
