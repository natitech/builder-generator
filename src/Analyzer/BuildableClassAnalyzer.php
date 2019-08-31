<?php

namespace Nati\BuilderGenerator\Analyzer;

use Nati\BuilderGenerator\Property\ConstructorPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\NonFluentSetterPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\PublicPropertyBuildStrategy;
use PhpParser\Error;
use PhpParser\ErrorHandler\Throwing;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

final class BuildableClassAnalyzer
{
    private $nodeFinder;

    private $ast;

    private $classNode;

    private $classMethodNodes;

    private $analysis;

    public function __construct()
    {
        $this->nodeFinder = new NodeFinder();
    }

    /**
     * @param string $classContent
     * @return \Nati\BuilderGenerator\Analyzer\BuildableClass
     * @throws \InvalidArgumentException if $classContent is not buildable
     */
    public function analyse(string $classContent): BuildableClass
    {
        $this->init($classContent);
        $this->addNamespace();
        $this->addClassName();
        $this->addProperties();
        $this->addNbConstructorArgs();

        return $this->analysis;
    }

    private function init(string $classContent): void
    {
        $this->ast              = $this->guardAst($classContent);
        $this->classNode        = $this->guardClassNode();
        $this->classMethodNodes = $this->nodeFinder->findInstanceOf($this->classNode, ClassMethod::class);
        $this->analysis         = new BuildableClass();
    }

    private function addNamespace(): void
    {
        $this->analysis->namespace = $this->getNamespace();
    }

    private function addClassName(): void
    {
        $this->analysis->name = (string)$this->classNode->name;
    }

    private function addProperties(): void
    {
        $propertyNodes = $this->nodeFinder->findInstanceOf($this->classNode, Property::class);

        foreach ($propertyNodes as $propertyNode) {
            if ($property = $this->makeBuildableProperty($propertyNode)) {
                $this->analysis->properties[] = $property;
            }
        }
    }

    private function addNbConstructorArgs(): void
    {
        $this->analysis->nbConstructorArgs = count($this->getConstructorArgs());
    }

    private function guardAst(string $classContent)
    {
        try {
            return (new ParserFactory)->create(ParserFactory::PREFER_PHP7)->parse($classContent, new Throwing());
        } catch (Error $e) {
            throw new \InvalidArgumentException('Not php code', null, $e);
        }
    }

    private function guardClassNode()
    {
        if (!($classNode = $this->nodeFinder->findFirstInstanceOf($this->ast, Class_::class))) {
            throw new \InvalidArgumentException('No class found');
        }

        return $classNode;
    }

    private function getNamespace()
    {
        if ($nsNode = $this->nodeFinder->findFirstInstanceOf($this->ast, Namespace_::class)) {
            return (string)$nsNode->name;
        }

        return null;
    }

    private function makeBuildableProperty(Property $propertyNode): ?BuildableProperty
    {
        if (!($propertyName = (string)($propertyNode->props[0]->name ?? null))) {
            return null;
        }

        $constructorInitializationPosition = $this->getConstructorInitializationPosition(
            $propertyName,
            $this->findMethod('__construct')
        );

        $property                   = new BuildableProperty();
        $property->name             = $propertyName;
        $property->inferredType     = 'string';
        $property->inferredFake     = 'word';
        $property->constructorOrder = $constructorInitializationPosition;
        $property->writeStrategies  = $this->getWriteStrategies($propertyNode, $constructorInitializationPosition);

        return $property;
    }

    private function getConstructorInitializationPosition(string $propertyName, $constructorNode): ?int
    {
        if ($constructorNode
            && ($constructorArgs = $this->getConstructorArgs())
            && ($assignments = $this->nodeFinder->findInstanceOf($constructorNode, Assign::class))) {
            foreach ($assignments as $assignment) {
                $assigned = ((string)($assignment->var->var->name ?? null)) . ((string)($assignment->var->name ?? null));
                $to       = (string)($assignment->expr->name ?? null);

                if (($assigned === 'this' . $propertyName) && in_array($to, $constructorArgs, true)) {
                    return array_search($to, $constructorArgs, true);
                }
            }
        }

        return null;
    }

    private function getWriteStrategies(Property $propertyNode, ?int $constructorInitializationPosition): array
    {
        $propertyName = (string)($propertyNode->props[0]->name ?? null);

        $writeStrategies = [];

        if ($propertyNode->isPublic()) {
            $writeStrategies[] = PublicPropertyBuildStrategy::class;
        }

        if ($this->hasSetterForProperty($propertyName)) {
            $writeStrategies[] = NonFluentSetterPropertyBuildStrategy::class;
        }

        if ($constructorInitializationPosition !== null) {
            $writeStrategies[] = ConstructorPropertyBuildStrategy::class;
        }

        return $writeStrategies;
    }

    private function hasSetterForProperty(string $propertyName): bool
    {
        return (boolean)$this->findMethod('set' . ucfirst($propertyName));
    }

    private function findMethod(string $functionName)
    {
        if (!$this->classMethodNodes) {
            return null;
        }

        foreach ($this->classMethodNodes as $methodNode) {
            /** @var ClassMethod $methodNode */
            if (((string)$methodNode->name) === $functionName && $methodNode->isPublic()) {
                return $methodNode;
            }
        }

        return null;
    }

    private function getConstructorArgs()
    {
        return $this->getMethodArgNames($this->findMethod('__construct'));
    }

    private function getMethodArgNames($methodNode): array
    {
        if (!$methodNode) {
            return [];
        }

        $constructorArgs = [];
        foreach ($methodNode->params as $param) {
            $constructorArgs[] = (string)$param->var->name;
        }

        return $constructorArgs;
    }
}
