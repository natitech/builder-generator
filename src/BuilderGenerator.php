<?php

namespace Nati\BuilderGenerator;

use Faker\Generator;
use Nati\BuilderGenerator\Property\PropertyBuildStrategy;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;

final class BuilderGenerator
{
    public function getBuilderContent($fqn, PropertyBuildStrategy $propertyBuildStrategy): string
    {
        try {
            $builtClass = ClassType::from($fqn);
        } catch (\ReflectionException $e) {
            throw new \InvalidArgumentException('Class not loaded');
        }

        $builtClassName = $builtClass->getName();
        $properties     = array_map(
            function ($property) {
                /** @var \Nette\PhpGenerator\Property $property */
                return $property->getName();
            }, $builtClass->getProperties()
        );

        $builderClass = new ClassType($builtClassName . 'Builder');
        $builderClass->setFinal();

        $constructorBody = '';

        foreach ($properties as $property) {
            $builderClass->addProperty($property)
                         ->setVisibility(ClassType::VISIBILITY_PRIVATE);

            $constructorBody .= "\n" . sprintf('$this->%s = $faker->word;', $property);
        }

        $builderClass->addMethod('__construct')
                     ->addBody($constructorBody)
                     ->addParameter('faker')
                     ->setTypeHint(Generator::class);

        $builderClass->addMethod('build')
                     ->setReturnType($fqn)
                     ->addBody($propertyBuildStrategy->getBuildFunctionBody($builtClassName, $properties));

        $namespace = new PhpNamespace($this->getNamespace($fqn, $builtClassName));
        $namespace->addUse(Generator::class);
        $namespace->addUse($fqn);
        $namespace->add($builderClass);

        return '<?php' . "\n" . "\n" . (new PsrPrinter())->printNamespace($namespace);
    }

    private function getNamespace(string $fullyQualifiedName, string $relativeName)
    {
        return substr($fullyQualifiedName, 0, strrpos($fullyQualifiedName, $relativeName) - 1);
    }
}
