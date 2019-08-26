<?php

namespace Nati\BuilderGenerator\Test\Unit;

use Nati\BuilderGenerator\BuilderGenerator;
use PHPUnit\Framework\TestCase;
use Nati\BuilderGenerator\Test\Fixtures\Test;

class BuilderGeneratorTest extends TestCase
{
    /** @var \Nati\BuilderGenerator\BuilderGenerator */
    private $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = new BuilderGenerator();
    }

    /**
     * @test
     */
    public function whenClassNotLoadedThenThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->generator->getBuilderContent('Foobar\Test');
    }

    /**
     * @test
     */
    public function canGenerateClassNameAndNamespace()
    {
        $this->assertBuilderContentContains('namespace Nati\BuilderGenerator\Test\Fixtures;');
        $this->assertBuilderContentContains('class TestBuilder');
    }

    /**
     * @test
     */
    public function canGenerateBuildFunction()
    {
        $this->assertBuilderContentContains('public function build()');
    }

    /**
     * @test
     */
    public function canGenerateProperties()
    {
        $this->assertBuilderContentContains('private $test;');
    }

    /**
     * @test
     */
    public function canGenerateConstructor()
    {
        $this->assertBuilderContentContains('__construct(Generator $faker)');
        $this->assertBuilderContentContains('$this->test = $faker->word;');
    }

    private function assertBuilderContentContains(string $expected): void
    {
        $this->assertStringContainsString($expected, $this->generator->getBuilderContent(Test::class));
    }
}
