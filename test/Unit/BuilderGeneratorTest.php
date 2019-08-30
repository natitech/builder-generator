<?php

namespace Nati\BuilderGenerator\Test\Unit;

use Nati\BuilderGenerator\BuilderGenerator;
use Nati\BuilderGenerator\Test\Double\Property\PropertyBuildStrategyStub;
use Nati\BuilderGenerator\Test\Fixtures\TestPublic;
use PHPUnit\Framework\TestCase;

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

        $this->generator->getBuilderContent('Foobar\Test', new PropertyBuildStrategyStub());
    }

    /**
     * @test
     */
    public function canGenerateClassNameAndNamespace()
    {
        $this->assertBuilderContentContains('namespace Nati\BuilderGenerator\Test\Fixtures;');
        $this->assertBuilderContentContains('class TestPublicBuilder');
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

    /**
     * @test
     */
    public function canGenerateBuildFunction()
    {
        $this->assertBuilderContentContains('public function build()');
        $this->assertBuilderContentContains('body');
    }

    private function assertBuilderContentContains(string $expected): void
    {
        $this->assertStringContainsString(
            $expected,
            $this->generator->getBuilderContent(TestPublic::class, new PropertyBuildStrategyStub())
        );
    }
}
