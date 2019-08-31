<?php

namespace Nati\BuilderGenerator;

use Nati\BuilderGenerator\Analyzer\BuildableClassAnalyzer;
use Nati\BuilderGenerator\Driver\Filesystem;
use Nati\BuilderGenerator\Property\PropertyBuildStrategyAutoResolver;

final class FileBuilderGenerator
{
    private $fs;

    private $classAnalyzer;

    private $generator;

    public function __construct(Filesystem $fs, BuildableClassAnalyzer $classAnalyzer, BuilderGenerator $generator)
    {
        $this->fs            = $fs;
        $this->classAnalyzer = $classAnalyzer;
        $this->generator     = $generator;
    }

    public static function create(): self
    {
        return new self(
            new Filesystem(),
            new BuildableClassAnalyzer(),
            new BuilderGenerator(new PropertyBuildStrategyAutoResolver())
        );
    }

    public function generateFrom($classFilePath): void
    {
        $this->fs->writeNear(
            $classFilePath,
            'Builder',
            $this->generator->getBuilderClassContent($this->classAnalyzer->analyse($this->fs->read($classFilePath)))
        );
    }
}
