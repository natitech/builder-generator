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
    private PropertyBuildStrategyResolver $strategyResolver;

    private BuildableClass $builtClass;

    private ClassType $builderClass;

    private PsrPrinter $printer;

    public function __construct(PropertyBuildStrategyResolver $strategyResolver)
    {
        $this->strategyResolver = $strategyResolver;
        $this->printer          = new PsrPrinter();
    }

    public function getBuilderClassContent(BuildableClass $buildableClass, ?string $strategy): string
    {
        $strategy = $strategy ?: $this->getMostUsedStrategyClassAmong($buildableClass->properties);

        $this->init($buildableClass, $strategy);
        $this->addProperties();
        $this->addConstructor();
        $this->addBuildMethod($strategy);

        return $this->dump();
    }

    private function init(BuildableClass $buildableClass, ?string $strategy): void
    {
        $this->builtClass             = clone $buildableClass;
        $this->builtClass->properties = $this->getPropertiesThatSupport($buildableClass->properties, $strategy);

        $this->builderClass = new ClassType($this->builtClass->name . 'Builder');
        $this->builderClass->setFinal();
    }

    private function addProperties(): void
    {
        foreach ($this->builtClass->properties as $property) {
            $this->builderClass->addProperty($property->name)
                               ->setType($property->inferredType)
                               ->setVisibility(ClassType::VISIBILITY_PRIVATE);
        }
    }

    private function addConstructor(): void
    {
        $constructorBody = '';
        foreach ($this->builtClass->properties as $property) {
            $constructorBody .= "\n" . sprintf(
                    '$this->%s = %s;',
                    $property->name,
                    $property->inferredFake ?: 'null'
                );
        }

        $this->builderClass->addMethod('__construct')
                           ->addBody($constructorBody)
                           ->addParameter('faker')
                           ->setType(Generator::class);
    }

    private function addBuildMethod(?string $strategy): void
    {
        $this->builderClass->addMethod('build')
                           ->setReturnType($this->getBuiltClassFQN())
                           ->addBody($this->getBuildFunctionBody($this->builtClass, $strategy));
    }

    private function dump(): string
    {
        return '<?php' . "\n\n" . ($this->builtClass->namespace ?
                $this->printer->printNamespace($this->getNamespace()) :
                $this->printer->printClass($this->builderClass));
    }

    private function getNamespace(): PhpNamespace
    {
        $namespace = new PhpNamespace($this->builtClass->namespace);
        $namespace->addUse(Generator::class);
        $namespace->addUse($this->getBuiltClassFQN());
        $namespace->add($this->builderClass);

        return $namespace;
    }

    private function getBuildFunctionBody(BuildableClass $builtClass, ?string $strategy): string
    {
        if (!$strategy) {
            return 'return new ' . $builtClass->name . '();';
        }

        return $this->strategyResolver->resolve($strategy)
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

    private function getBuiltClassFQN(): string
    {
        return $this->builtClass->namespace . '\\' . $this->builtClass->name;
    }
}
