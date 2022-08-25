<?php

namespace Nati\BuilderGenerator\Test\Unit;

use Nati\BuilderGenerator\BuilderGenerator;
use Nati\BuilderGenerator\Test\Double\Property\PropertyBuildStrategyResolverMock;

class BuilderGeneratorTest extends UnitTest
{
    private BuilderGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = new BuilderGenerator(new PropertyBuildStrategyResolverMock());
    }

    /**
     * @test
     */
    public function canGenerateClassNameAndNamespace()
    {
        $builderClassContent = $this->getBuilderClassContent();

        $this->assertBuilderClassCodeContains('namespace Nati\BuilderGenerator\Test\Fixtures;', $builderClassContent);
        $this->assertBuilderClassCodeContains('class TestPublicBuilder', $builderClassContent);
    }

    /**
     * @test
     */
    public function canGenerateBuilderForClassWithoutProperties()
    {
        $this->assertBuilderClassCodeContains(
            'public function build(): TestPublic { return new TestPublic(); }',
            $this->getBuilderClassContent()
        );
    }

    /**
     * @test
     */
    public function canAddProperties()
    {
        $this->assertBuilderClassCodeContains(
            'private $prop1;',
            $this->getBuilderClassContent([$this->makeProperty()])
        );
    }

    /**
     * @test
     */
    public function canGenerateConstructor()
    {
        $this->assertBuilderClassCodeContains(
            '__construct(Generator $faker) { $this->prop1 = $faker->word; }',
            $this->getBuilderClassContent([$this->makeProperty()])
        );
    }

    /**
     * @test
     */
    public function canGenerateBuildFunction()
    {
        $this->assertBuilderClassCodeContains(
            'public function build(): TestPublic { return null; }',
            $this->getBuilderClassContent([$this->makeProperty()])
        );
    }

    /**
     * @test
     */
    public function canUseMostUsedStrategyOnRelevantProperties()
    {
        $builderClassContent = $this->getBuilderClassContent(
            [
                $this->makeProperty('prop1', $this->mixedStrategies()),
                $this->makeProperty('prop2', $this->commentStrategies()),
                $this->makeProperty('prop3', $this->commentStrategies()),
                $this->makeProperty('prop4', $this->nullStrategies())
            ]
        );

        $this->assertBuilderClassCodeContains(
            'public function __construct(Generator $faker) { $this->prop1 = $faker->word; $this->prop2 = $faker->word; $this->prop3 = $faker->word; }',
            $builderClassContent
        );
        $this->assertBuilderClassCodeContains(
            'public function build(): TestPublic { //CommentPropertyBuildStrategy with 3 properties }',
            $builderClassContent
        );
    }

    private function assertBuilderClassCodeContains(string $expected, $builderClassContent)
    {
        $this->assertStringContainsString(
            $this->spaceless($expected),
            $this->spaceless($builderClassContent),
            'Code equivalent to "' . $expected . '" not found in "' . $builderClassContent . '"'
        );
    }

    private function getBuilderClassContent(array $properties = []): string
    {
        return $this->generator->getBuilderClassContent($this->makeClass($properties));
    }

    private function spaceless(string $expected): string
    {
        return str_replace([' ', "\n"], '', $expected);
    }
}
