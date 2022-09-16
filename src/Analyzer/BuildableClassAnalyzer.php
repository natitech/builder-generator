<?php

namespace Nati\BuilderGenerator\Analyzer;

use Nati\BuilderGenerator\Driver\PhpDocParser;
use PhpParser\Error;
use PhpParser\ErrorHandler\Throwing;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Psr\Log\LoggerInterface;

final class BuildableClassAnalyzer
{
    private const DOC_TO_PHP_TYPE = [
        'string'             => 'string',
        'float'              => 'float',
        'int'                => 'int',
        'integer'            => 'int',
        'boolean'            => 'bool',
        'bool'               => 'bool',
        'date'               => '\DateTime',
        'datetime'           => '\DateTime',
        '\DateTime'          => '\DateTime',
        'DateTime'           => '\DateTime',
        '\DateTimeImmutable' => '\DateTimeImmutable',
        'DateTimeImmutable'  => '\DateTimeImmutable'
    ];

    private Parser $phpParser;

    private NodeFinder $nodeFinder;

    private PhpDocParser $docParser;

    private LoggerInterface $logger;

    /** @var Node\Stmt[] */
    private array $ast;

    private Node $classNode;

    /** @var Node[] */
    private array $classMethodNodes;

    private BuildableClass $analysis;

    public function __construct(LoggerInterface $logger)
    {
        $this->phpParser  = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->nodeFinder = new NodeFinder();
        $this->docParser  = new PhpDocParser();
        $this->logger     = $logger;
    }

    /**
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

    private function guardAst(string $classContent): array
    {
        try {
            return $this->phpParser->parse($classContent, new Throwing());
        } catch (Error $e) {
            throw new \InvalidArgumentException('Not php code', 0, $e);
        }
    }

    private function guardClassNode(): Node
    {
        if (!($classNode = $this->nodeFinder->findFirstInstanceOf($this->ast, Class_::class))) {
            throw new \InvalidArgumentException('No class found');
        }

        return $classNode;
    }

    private function getNamespace(): ?string
    {
        if ($nsNode = $this->nodeFinder->findFirstInstanceOf($this->ast, Namespace_::class)) {
            return (string)$nsNode->name;
        }

        return null;
    }

    private function makeBuildableProperty(Property $node): ?BuildableProperty
    {
        if (!($name = (string)($node->props[0]->name ?? null))) {
            return null;
        }

        $constructorInitializationPosition = $this->getConstructorInitializationPosition(
            $name,
            $this->getConstructorNode()
        );

        $property                   = new BuildableProperty();
        $property->name             = $name;
        $property->inferredType     = $this->inferType($name, $node, $constructorInitializationPosition);
        $property->inferredFake     = $this->inferFake($name, $property->inferredType);
        $property->constructorOrder = $constructorInitializationPosition;
        $property->writeStrategies  = $this->getWriteStrategies($name, $node, $constructorInitializationPosition);

        return $property;
    }

    private function getConstructorInitializationPosition(string $name, $constructorNode): ?int
    {
        if ($constructorNode
            && ($constructorArgs = $this->getConstructorArgs())
            && ($assignments = $this->nodeFinder->findInstanceOf($constructorNode, Assign::class))) {
            foreach ($assignments as $assignment) {
                $assigned = ((string)($assignment->var->var->name ?? null)) . ((string)($assignment->var->name ?? null));
                $to       = (string)($assignment->expr->name ?? null);

                if (($assigned === 'this' . $name) && in_array($to, $constructorArgs, true)) {
                    return array_search($to, $constructorArgs, true);
                }
            }
        }

        return null;
    }

    private function inferType(string $name, Property $node, ?int $constructorInitializationPosition): ?string
    {
        if ($type = $node->type) {
            $this->logger->debug('Found type from php for {name}', ['name' => $name]);

            return $type instanceof Node\NullableType ? $type->type->toString() : $type->toString();
        }

        if ($type = $this->getTypeFromAttributes($node)) {
            $this->logger->debug('Found type from attributes for {name}', ['name' => $name]);

            return $type;
        }

        if (($comments = $node->getComments())
            && ($type = $this->docParser->getType((string)$comments[0]))) {
            $this->logger->debug('Found type from phpdoc for {name}', ['name' => $name]);

            return $this->toPhpType($this->cleanNullable($type));
        }

        if ($constructorInitializationPosition !== null) {
            $this->logger->debug('Found type from constructor for {name}', ['name' => $name]);

            return $this->getConstructorArgumentType($constructorInitializationPosition);
        }

        return null;
    }

    private function inferFake(string $name, ?string $type): ?string
    {
        if (!$type || !$this->isFakeSupportedType($type)) {
            $this->logger->debug('No fake supported for {name}', ['name' => $name]);

            return null;
        }

        if ($type === 'string') {
            return $this->guessStringFakeFunction($name);
        }

        if ($type === 'float') {
            return '$faker->randomFloat()';
        }

        if ($type === 'int' || $type === 'integer') {
            return '$faker->randomNumber()';
        }

        if ($type === 'boolean' || $type === 'bool' || (!$type && preg_match('/^is[_A-Z]/', $name))) {
            return '$faker->boolean';
        }

        if (stripos($type, 'date') !== false) {
            if (stripos($type, 'immutable') !== false) {
                return '\DateTimeImmutable::createFromMutable($faker->dateTime)';
            }

            return '$faker->dateTime';
        }

        return null;
    }

    private function getWriteStrategies(
        string $name,
        Property $propertyNode,
        ?int $constructorInitializationPosition
    ): array {
        $writeStrategies = [];

        if ($propertyNode->isPublic()) {
            $writeStrategies[] = 'public';
        }

        if ($setter = $this->getSetterForProperty($name)) {
            $writeStrategies[] = $this->isFluent($setter) ? 'fluent_setter' : 'setter';
        }

        if ($constructorInitializationPosition !== null) {
            $writeStrategies[] = 'constructor';
        }

        if (!$writeStrategies) {
            $writeStrategies[] = 'build_method';
        }

        $this->logger->debug(
            'Found {strategies} write strategies for {name}',
            ['strategies' => count($writeStrategies), 'name' => $name]
        );

        return $writeStrategies;
    }

    private function getSetterForProperty(string $name): ?ClassMethod
    {
        return $this->findMethod('set' . ucfirst($name));
    }

    private function isFluent(ClassMethod $method): bool
    {
        if ($return = $this->nodeFinder->findFirstInstanceOf($method, Return_::class)) {
            return isset($return->expr->name) && $return->expr->name === 'this';
        }

        return false;
    }

    private function findMethod(string $functionName): ?ClassMethod
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

    private function getConstructorArgs(): array
    {
        return $this->getMethodArgNames($this->getConstructorNode());
    }

    private function getMethodArgNames(?ClassMethod $methodNode): array
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

    private function isFakeSupportedType(string $propertyType): bool
    {
        return in_array(
            $propertyType,
            [
                'string',
                'float',
                'int',
                'integer',
                'boolean',
                'bool',
                'date',
                'datetime',
                '\DateTime',
                'DateTime',
                '\DateTimeImmutable',
                'DateTimeImmutable'
            ],
            true
        );
    }

    private function getConstructorArgumentType(int $constructorInitializationPosition): ?string
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
     * @see <https://github.com/fzaninotto/Faker/blob/master/src/Faker/Guesser/Name.php>
     */
    private function guessStringFakeFunction(string $propertyName): string
    {
        switch (str_replace('_', '', $propertyName)) {
            case 'firstname':
                return '$faker->firstName';
            case 'lastname':
                return '$faker->lastName';
            case 'username':
            case 'login':
                return '$faker->userName';
            case 'email':
            case 'emailaddress':
                return '$faker->email';
            case 'phonenumber':
            case 'phone':
            case 'telephone':
            case 'telnumber':
                return '$faker->phoneNumber';
            case 'address':
                return '$faker->address';
            case 'city':
            case 'town':
            case 'county':
                return '$faker->city';
            case 'streetaddress':
                return '$faker->streetAddress';
            case 'postcode':
            case 'zipcode':
                return '$faker->postcode';
            case 'state':
                return '$faker->state';
            case 'country':
                return '$faker->countryCode';
            case 'locale':
                return '$faker->locale';
            case 'currency':
            case 'currencycode':
                return '$faker->currencyCode';
            case 'url':
            case 'website':
                return '$faker->url';
            case 'company':
            case 'companyname':
            case 'employer':
                return '$faker->company';
            case 'title':
                return '$faker->title';
            case 'body':
            case 'summary':
            case 'article':
            case 'description':
                return '$faker->text';
        }

        return '$faker->word';
    }

    private function getConstructorNode(): ?ClassMethod
    {
        return $this->findMethod('__construct');
    }

    private function toPhpType(?string $phpDocType): ?string
    {
        return self::DOC_TO_PHP_TYPE[$phpDocType] ?? null;
    }

    private function cleanNullable(?string $phpDocType)
    {
        $phpDocType = str_replace(['(', ')'], '', $phpDocType);
        $types      = explode(' | ', $phpDocType);

        if (count($types) > 1) {
            foreach ($types as $type) {
                if ($type !== 'null') {
                    return $type;
                }
            }
        }

        return $phpDocType;
    }

    private function getTypeFromAttributes(Property $propertyNode): ?string
    {
        if ($ormAttributes = $this->getORMAttribute($propertyNode)) {
            foreach ($ormAttributes as $ormAttribute) {
                /** @var ?Node\Attribute $ormAttribute */
                foreach ($ormAttribute->args as $arg) {
                    if ((string)$arg->name === 'type') {
                        return (string)$arg->value->value;
                    }
                }
            }

            return 'string';
        }

        return null;
    }

    private function getORMAttribute(Property $propertyNode): array
    {
        $attributes = [];
        foreach ($propertyNode->attrGroups as $group) {
            foreach ($group->attrs as $attr) {
                if (in_array('ORM', $attr->name->parts, true)) {
                    $attributes[] = $attr;
                }
            }
        }

        return $attributes;
    }
}
