<?php

namespace Nati\BuilderGenerator\Test\Integration;

use Nati\BuilderGenerator\FileBuilderGenerator;
use PHPUnit\Framework\TestCase;

final class GeneratorTest extends TestCase
{
    /** @var FileBuilderGenerator */
    private $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = FileBuilderGenerator::create();
    }

    /**
     * @test
     */
    public function canCreateBuilderFileUsingPublicStrategy()
    {
        $this->generateBuilderForFixture('TestPublic');

        $this->assertBuilderClassExists('TestPublicBuilder');
    }

    /**
     * @test
     */
    public function canCreateBuilderFileUsingSetterStrategy()
    {
        $this->generateBuilderForFixture('TestNonFluentSetter');

        $this->assertBuilderClassExists('TestNonFluentSetterBuilder');
    }

    /**
     * @test
     */
    public function canCreateBuilderFileUsingConstructorStrategy()
    {
        $this->generateBuilderForFixture('TestConstructor');

        $this->assertBuilderClassExists('TestConstructorBuilder');
    }

    private function generateBuilderForFixture(string $fixtureBuiltClass): void
    {
        $this->generator->generateFrom(__DIR__ . '/../Fixtures/' . $fixtureBuiltClass . '.php');
    }

    private function assertBuilderClassExists(string $expectedBuilderClass): void
    {
        $this->assertFileExists(__DIR__ . '/../Fixtures/' . $expectedBuilderClass . '.php');
        $builderFileContent = file_get_contents(__DIR__ . '/../Fixtures/' . $expectedBuilderClass . '.php');
        $this->assertStringContainsString('class ' . $expectedBuilderClass . '', $builderFileContent);
    }
}
