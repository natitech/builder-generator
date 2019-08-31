<?php

namespace Nati\BuilderGenerator;

use Faker\Generator;
use Nati\BuilderGenerator\Analyzer\BuildableClass;
use Nati\BuilderGenerator\Property\PropertyBuildStrategyResolver;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;

final class BuilderGenerator
{
    private $strategyResolver;

    public function __construct(PropertyBuildStrategyResolver $strategyResolver)
    {
        $this->strategyResolver = $strategyResolver;
    }

    public function getBuilderClassContent(BuildableClass $buildableClass): string
    {
        $builderClass = new ClassType($buildableClass->name . 'Builder');
        $builderClass->setFinal();

        $mostUsedStrategy = $this->getMostUsedStrategyClassAmong($buildableClass->properties);

        $builtClass             = clone $buildableClass;
        $builtClass->properties = $this->getPropertiesThatSupport($buildableClass->properties, $mostUsedStrategy);

        $constructorBody = '';
        foreach ($builtClass->properties as $property) {
            $builderClass->addProperty($property->name)
                         ->addComment($property->inferredType ? '@var ' . $property->inferredType : null)
                         ->setVisibility(ClassType::VISIBILITY_PRIVATE);

            $constructorBody .= "\n" . sprintf(
                    '$this->%s = $faker->%s;',
                    $property->name,
                    $property->inferredFake ?: 'word'
                );
        }

        $builderClass->addMethod('__construct')
                     ->addBody($constructorBody)
                     ->addParameter('faker')
                     ->setTypeHint(Generator::class);

        $fqn = $builtClass->namespace . '\\' . $builtClass->name;

        $builderClass->addMethod('build')
                     ->setReturnType($fqn)
                     ->addBody($this->getBuildFunctionBody($builtClass, $mostUsedStrategy));

        $namespace = new PhpNamespace($builtClass->namespace);
        $namespace->addUse(Generator::class);
        $namespace->addUse($fqn);
        $namespace->add($builderClass);

        return '<?php' . "\n" . "\n" . (new PsrPrinter())->printNamespace($namespace);
    }

    private function getBuildFunctionBody(BuildableClass $builtClass, $strategy): string
    {
        if (!$builtClass->properties) {
            return 'return new ' . $builtClass->name . '();';
        }

        return $this->strategyResolver->resolveStrategy($strategy)
                                      ->getBuildFunctionBody($builtClass);
    }

    private function getMostUsedStrategyClassAmong(array $properties): ?string
    {
        if (!$properties) {
            return null;
        }

        $counts = [];
        foreach ($properties as $property) {
            /** @var \Nati\BuilderGenerator\Analyzer\BuildableProperty $property */
            foreach ($property->writeStrategies as $writeStrategy) {
                $counts[$writeStrategy] = isset($counts[$writeStrategy]) ? $counts[$writeStrategy] + 1 : 1;
            }
        }

        return array_search(max($counts), $counts, true);
    }

    private function getPropertiesThatSupport(array $properties, ?string $mostUsedStrategy): array
    {
        if (!$mostUsedStrategy) {
            return [];
        }

        return array_filter(
            $properties,
            static function ($property) use ($mostUsedStrategy) {
                /** @var \Nati\BuilderGenerator\Analyzer\BuildableProperty $property */
                return in_array($mostUsedStrategy, $property->writeStrategies, true);
            }
        );
    }
}
