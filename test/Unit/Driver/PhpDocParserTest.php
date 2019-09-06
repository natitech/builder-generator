<?php

namespace Nati\BuilderGenerator\Test\Unit\Driver;

use Nati\BuilderGenerator\Driver\PhpDocParser;
use Nati\BuilderGenerator\Test\Unit\UnitTest;

final class PhpDocParserTest extends UnitTest
{
    private $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new PhpDocParser();
    }

    /**
     * @test
     */
    public function whenNoFoundableTypeThenReturnNull()
    {
        $this->assertTypeIs(
            null,
            <<<PHPDOC
/**
 * A description
 * @custom My tag
 */
PHPDOC
        );
    }

    /**
     * @test
     */
    public function whenVarDocumentedTypeThenReturnType()
    {
        $this->assertTypeIs(
            'string',
            <<<PHPDOC
/**
 * Another description
 * @var string
 */
PHPDOC
        );
    }

    /**
     * @test
     */
    public function whenDoctrineORMDocumentedTypeThenReturnType()
    {
        $this->assertTypeIs(
            'string',
            <<<PHPDOC
/**
 * @ORM\Column(type="string", length=255)
 */
PHPDOC
        );
    }

    private function assertTypeIs($expectedType, string $phpdoc): void
    {
        $this->assertEquals($expectedType, $this->parser->getType($phpdoc));
    }
}
