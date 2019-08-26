<?php

namespace Nati\BuilderGenerator;

use Faker\Generator;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;

final class BuilderGenerator
{
    public function getBuilderContent($fqn): string
    {
        try {
            $builtClass = ClassType::from($fqn);
        } catch (\ReflectionException $e) {
            throw new \InvalidArgumentException('Class not loaded');
        }

        $builderClass = new ClassType($builtClass->getName() . 'Builder');
        $builderClass->setFinal();

        if (!$this->isPublicBuildStrategy($builtClass)) {
            throw new \LogicException('not yet implemented');
        }

        $constructorBody = '';
        $buildBody       = '$built = new ' . $builtClass->getName() . '();';
        foreach ($builtClass->getProperties() as $property) {
            $propertyName = $property->getName();

            $builderClass->addProperty($propertyName)
                         ->setVisibility(ClassType::VISIBILITY_PRIVATE);

            $constructorBody .= "\n" . sprintf('$this->%s = $faker->word;', $propertyName);
            $buildBody       .= "\n" . sprintf('$built->%s = $this->%s;', $propertyName, $propertyName);
        }
        $buildBody .= "\n\n" . 'return $built;';

        $builderClass->addMethod('__construct')
                     ->addBody($constructorBody)
                     ->addParameter('faker')
                     ->setTypeHint(Generator::class);

        $builderClass->addMethod('build')
                     ->addBody($buildBody);

        $namespace = new PhpNamespace($this->getNamespace($fqn, $builtClass->getName()));
        $namespace->addUse(Generator::class);
        $namespace->addUse($fqn);
        $namespace->add($builderClass);

        return '<?php' . "\n" . "\n" . (new PsrPrinter())->printNamespace($namespace);
    }

    private function getNamespace(string $fullyQualifiedName, string $relativeName)
    {
        return substr($fullyQualifiedName, 0, strrpos($fullyQualifiedName, $relativeName) - 1);
    }

    private function isPublicBuildStrategy(ClassType $builtClass)
    {
        return $this->hasPublicProperty($builtClass);
    }

    private function hasPublicProperty(ClassType $builtClass)
    {
        foreach ($builtClass->getProperties() as $property) {
            if ($property->getVisibility() === ClassType::VISIBILITY_PUBLIC) {
                return true;
            }
        }

        return false;
    }
}
