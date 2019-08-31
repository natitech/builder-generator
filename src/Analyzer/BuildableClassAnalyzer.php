<?php

namespace Nati\BuilderGenerator\Analyzer;

use Nati\BuilderGenerator\Driver\PhpDocParser;
use Nati\BuilderGenerator\Property\ConstructorPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\FluentSetterPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\NonFluentSetterPropertyBuildStrategy;
use Nati\BuilderGenerator\Property\PublicPropertyBuildStrategy;
use PhpParser\Error;
use PhpParser\ErrorHandler\Throwing;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

final class BuildableClassAnalyzer
{
    private $phpParser;

    private $nodeFinder;

    private $docParser;

    private $ast;

    private $classNode;

    private $classMethodNodes;

    private $analysis;

    public function __construct()
    {
        $this->phpParser  = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->nodeFinder = new NodeFinder();
        $this->docParser  = new PhpDocParser();
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
            return $this->phpParser->parse($classContent, new Throwing());
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
            $this->getConstructorNode()
        );

        $property                   = new BuildableProperty();
        $property->name             = $propertyName;
        $property->inferredType     = $this->inferType($propertyNode, $constructorInitializationPosition);
        $property->inferredFake     = $this->inferFake($propertyName, $property->inferredType);
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

    private function inferType(Property $propertyNode, ?int $constructorInitializationPosition): ?string
    {
        if (($comments = $propertyNode->getComments())
            && ($type = $this->docParser->getType((string)$comments[0]))) {
            return $type;
        }

        if ($constructorInitializationPosition !== null) {
            return $this->getConstructorArgumentType($constructorInitializationPosition);
        }

        return null;
    }

    private function inferFake(string $propertyName, ?string $propertyType): ?string
    {
        if (!$propertyType || !$this->isScalar($propertyType)) {
            return null;
        }

        if ($propertyType === 'string') {
            return $this->guessStringFakeFunction($propertyName);
        }

        if ($propertyType === 'float') {
            return 'randomFloat()';
        }

        if ($propertyType === 'int') {
            return 'randomNumber()';
        }

        if ($propertyType === 'boolean'
            || $propertyType === 'bool'
            || (!$propertyType && preg_match('/^is[_A-Z]/', $propertyName))) {
            return 'boolean';
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

        if ($setter = $this->getSetterForProperty($propertyName)) {
            $writeStrategies[] = $this->isFluent($setter) ?
                FluentSetterPropertyBuildStrategy::class :
                NonFluentSetterPropertyBuildStrategy::class;
        }

        if ($constructorInitializationPosition !== null) {
            $writeStrategies[] = ConstructorPropertyBuildStrategy::class;
        }

        return $writeStrategies;
    }

    private function getSetterForProperty(string $propertyName): ?ClassMethod
    {
        return $this->findMethod('set' . ucfirst($propertyName));
    }

    private function isFluent(ClassMethod $method): bool
    {
        if ($return = $this->nodeFinder->findFirstInstanceOf($method, Return_::class)) {
            return isset($return->expr->name) && $return->expr->name === 'this';
        }

        return false;
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
        return $this->getMethodArgNames($this->getConstructorNode());
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

    private function isScalar(string $propertyType)
    {
        return in_array($propertyType, ['string', 'float', 'int', 'boolean', 'bool'], true);
    }

    private function getConstructorArgumentType(int $constructorInitializationPosition)
    {
        $constructorNode = $this->getConstructorNode();

        foreach ($constructorNode->params as $index => $param) {
            if ($index === $constructorInitializationPosition) {
                return isset($param->type->name) ? (string)$param->type->name : null;
            }
        }

        return null;
    }

    /**
     * From <https://github.com/fzaninotto/Faker/blob/master/src/Faker/Guesser/Name.php>
     * @param $propertyName
     * @return string
     */
    private function guessStringFakeFunction($propertyName)
    {
        switch (str_replace('_', '', $propertyName)) {
            case 'firstname':
                return 'firstName';
            case 'lastname':
                return 'lastName';
            case 'username':
            case 'login':
                return 'userName';
            case 'email':
            case 'emailaddress':
                return 'email';
            case 'phonenumber':
            case 'phone':
            case 'telephone':
            case 'telnumber':
                return 'phoneNumber';
            case 'address':
                return 'address';
            case 'city':
            case 'town':
                return 'city';
            case 'streetaddress':
                return 'streetAddress';
            case 'postcode':
            case 'zipcode':
                return 'postcode';
            case 'state':
                return 'state';
            case 'county':
                return 'city';
                break;
            case 'country':
                return 'countryCode';
                break;
            case 'locale':
                return 'locale';
            case 'currency':
            case 'currencycode':
                return 'currencyCode';
            case 'url':
            case 'website':
                return 'url';
            case 'company':
            case 'companyname':
            case 'employer':
                return 'company';
            case 'title':
                return 'title';
            case 'body':
            case 'summary':
            case 'article':
            case 'description':
                return 'text';
        }

        return 'word';
    }

    private function getConstructorNode()
    {
        return $this->findMethod('__construct');
    }
}
