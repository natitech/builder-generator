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
    /**
     * @param string $classContent
     * @return \Nati\BuilderGenerator\Analyzer\BuildableClass
     * @throws \InvalidArgumentException if $classContent is not buildable
     */
    public function analyse(string $classContent): BuildableClass
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        try {
            $ast = $parser->parse($classContent, new Throwing());
        } catch (Error $e) {
            throw new \InvalidArgumentException('Not php code', null, $e);
        }

        $nodeFinder = new NodeFinder();

        $classNode = $nodeFinder->findFirstInstanceOf($ast, Class_::class);

        if (!$classNode) {
            throw new \InvalidArgumentException('No class found');
        }

        $nsNode = $nodeFinder->findFirstInstanceOf($ast, Namespace_::class);

        $class       = new BuildableClass();
        $class->name = (string)$classNode->name;

        if ($nsNode) {
            $class->namespace = (string)$nsNode->name;
        }

        $propertyNodes = $nodeFinder->findInstanceOf($classNode, Property::class);

        $constructorArgs = [];

        foreach ($propertyNodes as $propertyNode) {
            if ($propertyName = (string)($propertyNode->props[0]->name ?? null)) {
                $property = new BuildableProperty();

                $property->name            = $propertyName;
                $property->inferredType    = 'string';
                $property->inferredFake    = 'word';
                $property->writeStrategies = [];

                if ($propertyNode->isPublic()) {
                    $property->writeStrategies[] = PublicPropertyBuildStrategy::class;
                }

                if ($methods = $nodeFinder->findInstanceOf($classNode, ClassMethod::class)) {
                    foreach ($methods as $methodNode) {
                        $methodName = (string)$methodNode->name;
                        if ($methodName === 'set' . ucfirst($propertyName)) {
                            $property->writeStrategies[] = NonFluentSetterPropertyBuildStrategy::class;
                        }

                        if ($methodName === '__construct') {
                            $constructorArgs = [];
                            foreach ($methodNode->params as $param) {
                                $constructorArgs[] = (string)$param->var->name;
                            }
                            if ($constructorArgs) {
                                $assignments = $nodeFinder->findInstanceOf($methodNode, Assign::class);
                                foreach ($assignments as $assignment) {
                                    $assigned = ((string)($assignment->var->var->name ?? null)) . ((string)($assignment->var->name ?? null));
                                    $to       = (string)($assignment->expr->name ?? null);

                                    if ($assigned === 'this' . $propertyName) {
                                        $argsPosition = array_search($to, $constructorArgs, true);

                                        if ($argsPosition !== false) {
                                            $property->writeStrategies[] = ConstructorPropertyBuildStrategy::class;
                                            $property->constructorOrder  = $argsPosition;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $class->properties[] = $property;
            }
        }

        $class->nbConstructorArgs = count($constructorArgs);

        return $class;
    }
}
