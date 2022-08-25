<?php

namespace Nati\BuilderGenerator\Analyzer;

final class BuildableProperty
{
    public string $name;

    /** @var string[] */
    public array $writeStrategies = [];

    public ?int $constructorOrder;

    public ?string $inferredType;

    public ?string $inferredFake;

    public function __toString()
    {
        return $this->name;
    }
}
