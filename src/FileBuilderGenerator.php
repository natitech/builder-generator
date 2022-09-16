<?php

namespace Nati\BuilderGenerator;

use Nati\BuilderGenerator\Analyzer\BuildableClassAnalyzer;
use Nati\BuilderGenerator\Driver\Filesystem;
use Nati\BuilderGenerator\Property\BuildStrategy\ConstructorPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\BuildStrategy\FluentSetterPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\BuildStrategy\NonFluentSetterPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\BuildStrategy\PublicPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\BuildStrategy\StaticBuildMethodPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\PropertyBuildStrategyCollection;

final class FileBuilderGenerator
{
    private Filesystem $fs;

    private BuildableClassAnalyzer $classAnalyzer;

    private BuilderGenerator $generator;

    public function __construct(Filesystem $fs, BuildableClassAnalyzer $classAnalyzer, BuilderGenerator $generator)
    {
        $this->fs            = $fs;
        $this->classAnalyzer = $classAnalyzer;
        $this->generator     = $generator;
    }

    /** @api */
    public static function create(): self
    {
        return new self(
            new Filesystem(),
            new BuildableClassAnalyzer(),
            new BuilderGenerator(self::strategies())
        );
    }

    public static function strategies(): PropertyBuildStrategyCollection
    {
        return new PropertyBuildStrategyCollection(
            new ConstructorPropertyBuildStrategy(),
            new FluentSetterPropertyBuildStrategy(),
            new NonFluentSetterPropertyBuildStrategy(),
            new PublicPropertyBuildStrategy(),
            new StaticBuildMethodPropertyBuildStrategy()
        );
    }

    /** @api */
    public function generateFrom(string $classFilePath, ?string $explicityStrategy = null): void
    {
        $this->fs->writeNear(
            $classFilePath,
            'Builder',
            $this->generator->getBuilderClassContent(
                $this->classAnalyzer->analyse($this->fs->read($classFilePath)),
                $explicityStrategy
            )
        );
    }
}
