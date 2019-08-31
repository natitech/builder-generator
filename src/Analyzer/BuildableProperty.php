<?php

namespace Nati\BuilderGenerator\Analyzer;

final class BuildableProperty
{
    /** @var string */
    public $name;

    /** @var string[] */
    public $writeStrategies = [];

    /** @var int */
    public $constructorOrder;

    /** @var string */
    public $inferredType;

    /** @var string */
    public $inferredFake;

    public function __toString()
    {
        return (string)$this->name;
    }
}
