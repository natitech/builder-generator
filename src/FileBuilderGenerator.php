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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class FileBuilderGenerator
{
    private Filesystem $fs;

    private BuildableClassAnalyzer $classAnalyzer;

    private BuilderGenerator $generator;

    private LoggerInterface $logger;

    public function __construct(
        Filesystem $fs,
        BuildableClassAnalyzer $classAnalyzer,
        BuilderGenerator $generator,
        LoggerInterface $logger
    ) {
        $this->fs            = $fs;
        $this->classAnalyzer = $classAnalyzer;
        $this->generator     = $generator;
        $this->logger        = $logger;
    }

    /** @api */
    public static function create(?LoggerInterface $logger = null): self
    {
        $logger = $logger ?: new NullLogger();

        return new self(
            new Filesystem(),
            new BuildableClassAnalyzer($logger),
            new BuilderGenerator(self::strategies(), $logger),
            $logger
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
    public function generateFrom(
        string $builtClassFilePath,
        ?string $explicityStrategy = null,
        bool $withFakerSupport = false
    ): void {
        $this->logger->info(
            'Generating builder class for {builtClass} using {strategy} strategy {faker} faker support',
            [
                'builtClass' => $builtClassFilePath,
                'strategy'   => $explicityStrategy ?: 'automatic',
                'faker'      => $withFakerSupport ? 'with' : 'without'
            ]
        );

        $builderClassFilePath = $this->fs->writeNear(
            $builtClassFilePath,
            'Builder',
            $this->generator->getBuilderClassContent(
                $this->classAnalyzer->analyse($this->fs->read($builtClassFilePath)),
                $explicityStrategy,
                $withFakerSupport
            )
        );

        $this->logger->info('Builder class generated in {builderClass}', ['builderClass' => $builderClassFilePath]);
    }
}
