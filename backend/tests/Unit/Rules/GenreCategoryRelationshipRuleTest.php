<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Rules\GenreCategoryRelationshipRule;
use Mockery\MockInterface;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreCategoryRelationshipRuleTest extends TestCase
{
    public function testCategoriesIdField()
    {
        $rule = new GenreCategoryRelationshipRule([1, 1, 2, 2]);

        $reflectionClass = new \ReflectionClass(GenreCategoryRelationshipRule::class);
        $reflectionProperty = $reflectionClass->getProperty('categoriesId');
        $reflectionProperty->setAccessible(true);

        $categoriesId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1, 2], $categoriesId);
    }

    public function testPassesReturnsFalseWhenCategoriesOrGenresIsArrayEmpty()
    {
        $rule = $this->createRuleMock([1]);
        $this->assertFalse($rule->passes('', []));

        $rule = $this->createRuleMock([]);
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenGetCategoriesOfAGenreIsEmpty()
    {
        $rule = $this->createRuleMock([1]);
        $rule
            ->shouldReceive('getCategoriesOfAGenre')
            ->withAnyArgs()
            ->andReturn([]);

        $this->assertFalse($rule->passes('', [1]));

    }

    public function testPassesReturnsFalseWhenHasCategoriesWithoutGenres()
    {
        $rule = $this->createRuleMock([1, 2]);
        $rule
            ->shouldReceive('getCategoriesOfAGenre')
            ->withAnyArgs()
            ->andReturn([1]);
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesIsValid()
    {
        $rule = $this->createRuleMock([1, 2]);
        $rule->shouldReceive('getCategoriesOfAGenre')
            ->withAnyArgs()
            ->andReturn([1, 2]);

        $this->assertTrue($rule->passes('', [1]));

        $rule = $this->createRuleMock([1, 2]);
        $rule->shouldReceive('getCategoriesOfAGenre')
            ->withAnyArgs()
            ->andReturn([1, 2, 1, 2]);

        $this->assertTrue($rule->passes('', [1]));
    }

    protected function createRuleMock(array $categoriesId): MockInterface
    {
        return \Mockery::mock(GenreCategoryRelationshipRule::class, [$categoriesId])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }
}
