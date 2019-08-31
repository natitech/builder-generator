<?php

namespace Nati\BuilderGenerator\Analyzer;

use Nati\BuilderGenerator\Property\ConstructorPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\NonFluentSetterPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\PropertyBuildStrategy;
use Nati\BuilderGenerator\Property\PublicPropertyBuildStrategy;
use Nette\PhpGenerator\ClassType;

//TODO should use php parser instead

final class ClassHelper
{
    public function getFQN($fileContent): string
    {
        $namespace            = null;
        $class                = null;
        $nextTokenIsNamespace = false;
        $nextTokenIsClass     = false;

        foreach (token_get_all($fileContent) as $token) {
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                $nextTokenIsNamespace = true;
            }

            if (is_array($token) && $token[0] === T_CLASS) {
                $nextTokenIsClass = true;
            }

            if ($nextTokenIsNamespace) {
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR], true)) {
                    $namespace .= $token[1];
                } elseif ($token === ';') {
                    $nextTokenIsNamespace = false;
                }
            }

            if ($nextTokenIsClass && is_array($token) && $token[0] === T_STRING) {
                $class = $token[1];

                break;
            }
        }

        if ($class === null) {
            throw new \InvalidArgumentException('Not a class');
        }

        return $namespace ? $namespace . '\\' . $class : $class;
    }

    public function getPropertyBuildStrategy($fqn): PropertyBuildStrategy
    {
        try {
            $builtClass = ClassType::from($fqn);
        } catch (\ReflectionException $e) {
            throw new \InvalidArgumentException('Class not loaded');
        }

        if ($this->hasMostlyPublicProperties($builtClass)) {
            return new PublicPropertyBuildStrategy();
        }

        if ($this->arePropertiesMostlySetInConstructor($builtClass)) {
            return new ConstructorPropertyBuildStrategy();
        }

        return new NonFluentSetterPropertyBuildStrategy();
    }

    public function loadClass($classFilePath): void
    {
        /** @noinspection PhpIncludeInspection */
        require_once $classFilePath;
    }

    private function hasMostlyPublicProperties(ClassType $builtClass)
    {
        return $this->countProperties($builtClass, [ClassType::VISIBILITY_PUBLIC])
               >= $this->countProperties($builtClass, [ClassType::VISIBILITY_PROTECTED, ClassType::VISIBILITY_PRIVATE]);
    }

    private function countProperties(ClassType $builtClass, array $propertyTypes)
    {
        $count = 0;
        foreach ($builtClass->getProperties() as $property) {
            if (in_array($property->getVisibility(), $propertyTypes, true)) {
                $count++;
            }
        }

        return $count;
    }

    private function arePropertiesMostlySetInConstructor(ClassType $builtClass)
    {
        try {
            $contructor = $builtClass->getMethod('__construct');
        } catch (\Exception $e) {
            return false;
        }

        $properties = $builtClass->getProperties();

        return $this->countPropertiesSetInConstructor($contructor->getBody(), $properties) >= (count($properties) / 2);
    }

    private function countPropertiesSetInConstructor($constructorBody, array $properties)
    {
        $count = 0;
        foreach ($properties as $property) {
            if (strpos($constructorBody, '$this->' . $property->getName()) !== false) {
                $count++;
            }
        }

        return $count;
    }
}
