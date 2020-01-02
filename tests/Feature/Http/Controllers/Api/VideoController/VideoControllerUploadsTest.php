<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tests\Exceptions\TestException;
use Tests\Feature\Models\Video\BaseVideoTestCase;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerUploadsTest extends BaseVideoControllerTestCase
{
    use TestValidations;

    public function testInvalidationVideoFileField()
    {
        $file = UploadedFile::fake()->create('video.mp4')->size(50000001);
        $data = [
            'video_file' => $file
        ];
        $this->assertInvalidationInStoreAction($data, 'max.file', ['max' => 50000000]);
        $this->assertInvalidationInUpdateAction($data, 'max.file', ['max' => 50000000]);

        $file = UploadedFile::fake()->create('video.mkv');
        $data = [
            'video_file' => $file
        ];
        $this->assertInvalidationInStoreAction($data, 'mimetypes', ['values' => 'video/mp4']);
        $this->assertInvalidationInUpdateAction($data, 'mimetypes', ['values' => 'video/mp4']);
    }

    public function testSaveWithFileUpload()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);
        $file = UploadedFile::fake()->create('video.mp4');

        $data = $this->sendData + [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id],
                'video_file' => $file
            ];

        $response = $this->json('POST', route('videos.store'), $data);

        $response->assertStatus(201);
        \Storage::assertExists($response->json('id') . '/' . $file->hashName());

    }

    public function testUpdateWithFileUpload()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $data = $this->sendData + [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id],
            ] +
            $files;

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $data
        );

        $response->assertStatus(200);
        $id = $response->json('id');

        foreach ($files as $file) {
            \Storage::assertExists($id . '/' . $file->hashName());
        }

    }

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create('video_file.mp4')
        ];
    }
}
