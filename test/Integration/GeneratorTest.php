<?php

namespace Nati\BuilderGenerator\Test\Integration;

use Nati\BuilderGenerator\FileBuilderGenerator;
use PHPUnit\Framework\TestCase;

final class GeneratorTest extends TestCase
{
    private const TEST_CLASS_PATH = __DIR__ . '/../Fixtures/Test.php';

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
    public function canCreateBuilderFile()
    {
        $this->generator->generateFrom(self::TEST_CLASS_PATH);

        $this->assertFileExists(__DIR__ . '/../Fixtures/TestBuilder.php');
        $builderFileContent = file_get_contents(__DIR__ . '/../Fixtures/TestBuilder.php');
        $this->assertStringContainsString('class TestBuilder', $builderFileContent);
        $this->assertStringContainsString('build()', $builderFileContent);
        $this->assertStringContainsString('private $test;', $builderFileContent);
    }
}
