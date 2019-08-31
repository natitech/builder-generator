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

    /** @var BuildableClass */
    private $builtClass;

    /** @var ClassType */
    private $builderClass;

    public function __construct(PropertyBuildStrategyResolver $strategyResolver)
    {
        $this->strategyResolver = $strategyResolver;
    }

    public function getBuilderClassContent(BuildableClass $buildableClass): string
    {
        $mostUsedStrategy = $this->getMostUsedStrategyClassAmong($buildableClass->properties);

        $this->init($buildableClass, $mostUsedStrategy);
        $this->addProperties();
        $this->addConstructor();
        $this->addBuildMethod($mostUsedStrategy);

        return $this->dump($this->addNamespace());
    }

    private function init(BuildableClass $buildableClass, ?string $mostUsedStrategy): void
    {
        $this->builtClass             = clone $buildableClass;
        $this->builtClass->properties = $this->getPropertiesThatSupport($buildableClass->properties, $mostUsedStrategy);

        $this->builderClass = new ClassType($this->builtClass->name . 'Builder');
        $this->builderClass->setFinal();
    }

    private function addProperties(): void
    {
        foreach ($this->builtClass->properties as $property) {
            $this->builderClass->addProperty($property->name)
                               ->addComment($property->inferredType ? '@var ' . $property->inferredType : null)
                               ->setVisibility(ClassType::VISIBILITY_PRIVATE);
        }
    }

    private function addConstructor(): void
    {
        $constructorBody = '';
        foreach ($this->builtClass->properties as $property) {
            $constructorBody .= "\n" . sprintf(
                    '$this->%s = $faker->%s;',
                    $property->name,
                    $property->inferredFake ?: 'word'
                );
        }

        $this->builderClass->addMethod('__construct')
                           ->addBody($constructorBody)
                           ->addParameter('faker')
                           ->setTypeHint(Generator::class);
    }

    private function addBuildMethod(?string $mostUsedStrategy): void
    {
        $this->builderClass->addMethod('build')
                           ->setReturnType($this->getBuiltClassFQN())
                           ->addBody($this->getBuildFunctionBody($this->builtClass, $mostUsedStrategy));
    }

    private function addNamespace(): PhpNamespace
    {
        $namespace = new PhpNamespace($this->builtClass->namespace);
        $namespace->addUse(Generator::class);
        $namespace->addUse($this->getBuiltClassFQN());
        $namespace->add($this->builderClass);

        return $namespace;
    }

    private function dump(PhpNamespace $namespace): string
    {
        return '<?php' . "\n\n" . (new PsrPrinter())->printNamespace($namespace);
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

    private function getBuiltClassFQN(): string
    {
        return $this->builtClass->namespace . '\\' . $this->builtClass->name;
    }
}
