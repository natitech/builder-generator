<?php

namespace Nati\BuilderGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PsrPrinter;
use SebastianBergmann\CodeCoverage\Node\File;



final class FileBuilderGenerator
{
    private $fs;

    private $classloader;

    private $generator;

    public function __construct(Filesystem $fs, Classloader $classloader, BuilderGenerator $generator)
    {
        $this->fs          = $fs;
        $this->classloader = $classloader;
        $this->generator   = $generator;
    }

    public static function create(): self
    {
        return new self(new Filesystem(), new Classloader(), new BuilderGenerator());
    }

    public function generateFrom($classFilePath)
    {
        $fqn = $this->classloader->getFQN($this->fs->read($classFilePath));

        $this->classloader->loadClass($classFilePath);

        $builderContent = $this->generator->getBuilderContent($fqn);

        $this->fs->writeNear($classFilePath, 'Builder', $builderContent);
    }
}
