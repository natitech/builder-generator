<?php

namespace Nati\BuilderGenerator;

use Nati\BuilderGenerator\Driver\ClassHelper;
use Nati\BuilderGenerator\Driver\Filesystem;

final class FileBuilderGenerator
{
    private $fs;

    private $classHelper;

    private $generator;

    public function __construct(Filesystem $fs, ClassHelper $classHelper, BuilderGenerator $generator)
    {
        $this->fs          = $fs;
        $this->classHelper = $classHelper;
        $this->generator   = $generator;
    }

    public static function create(): self
    {
        return new self(new Filesystem(), new ClassHelper(), new BuilderGenerator());
    }

    public function generateFrom($classFilePath)
    {
        $fqn = $this->classHelper->getFQN($this->fs->read($classFilePath));

        $this->classHelper->loadClass($classFilePath);

        $builderContent = $this->generator->getBuilderContent($fqn, $this->classHelper->getPropertyBuildStrategy($fqn));

        $this->fs->writeNear($classFilePath, 'Builder', $builderContent);
    }
}
