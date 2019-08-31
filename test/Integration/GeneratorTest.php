<?php

namespace Nati\BuilderGenerator\Test\Integration;

use Nati\BuilderGenerator\FileBuilderGenerator;
use PHPUnit\Framework\TestCase;

final class GeneratorTest extends TestCase
{
    /** @var FileBuilderGenerator */
    private $generator;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::cleanFiles();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = FileBuilderGenerator::create();
    }

    private static function cleanFiles()
    {
        foreach (
            [
                'TestConstructorBuilder',
                'TestPublicBuilder',
                'TestNonFluentSetterBuilder',
                'TestUnbuildableConstructorBuilder'
            ] as $potentialFile
        ) {
            $filepath = self::getFixturesFilePath($potentialFile);
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }

    private static function getFixturesFilePath(string $filename): string
    {
        return __DIR__ . '/../Fixtures/' . $filename . '.php';
    }

    /**
     * @param string $testedClass
     * @test
     * @dataProvider validClasses
     */
    public function canCreateBuilderFile($testedClass)
    {
        $this->generateBuilderForFixture($testedClass);

        $this->assertBuilderClassExists($testedClass . 'Builder');
    }

    /**
     * @test
     */
    public function canCreateBuilderFileUsingConstructorStrategyAndUnusableConstructor()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->generateBuilderForFixture('TestUnbuildableConstructor');
    }

    public function validClasses()
    {
        return [['TestPublic'], ['TestNonFluentSetter'], ['TestFluentSetter'], ['TestConstructor']];
    }

    private function generateBuilderForFixture(string $fixtureBuiltClass): void
    {
        $this->generator->generateFrom(self::getFixturesFilePath($fixtureBuiltClass));
    }

    private function assertBuilderClassExists(string $expectedBuilderClass): void
    {
        $filePath = self::getFixturesFilePath($expectedBuilderClass);

        $this->assertFileExists($filePath);
        $this->assertStringContainsString('class ' . $expectedBuilderClass . '', file_get_contents($filePath));
    }
}
