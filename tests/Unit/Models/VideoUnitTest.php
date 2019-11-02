<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Traits\Uuid;
use App\Models\Video;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VideoUnitTest extends TestCase
{
    private $video;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = new Video();
    }

    public function testIfUseTraits()
    {
        $traits = [
            SoftDeletes::class, Uuid::class
        ];
        $videoTraits = array_keys(class_uses(Video::class));
        $this->assertEquals($traits, $videoTraits);
    }

    public function testFillableAttribute()
    {
        $fillable = [
            'title',
            'description',
            'year_launched',
            'opened',
            'rating',
            'duration',
        ];
        $this->assertEquals($fillable, $this->video->getFillable());
    }

    public function testDatesAttribute()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        $this->assertCount(count($dates), $this->video->getDates());
        foreach ($dates as $date) {
            $this->assertContains($date, $this->video->getDates());
        }
    }

    public function testCastsAttribute()
    {
        $casts = [
            'id' => 'string',
            'opened' => 'boolean',
            'year_launched' => 'integer',
            'duration' => 'integer'
        ];
        $this->assertEquals($casts, $this->video->getCasts());
    }

    public function testIncrementingAttribute()
    {
        $this->assertFalse($this->video->getIncrementing());
    }

    public function testCategoriesRelationshipIsBelongsToMany()
    {
        $this->assertEmpty($this->video->categories);
        $this->assertTrue(is_a($this->video->categories(), BelongsToMany::class));
    }

    public function testGenresRelationshipIsBelongsToMany()
    {
        $this->assertEmpty($this->video->genres);
        $this->assertTrue(is_a($this->video->genres(), BelongsToMany::class));
    }
}
