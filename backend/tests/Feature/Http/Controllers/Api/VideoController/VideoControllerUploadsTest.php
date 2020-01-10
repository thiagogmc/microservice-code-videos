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

    public function testInvalidationThumbField()
    {
        $file = UploadedFile::fake()->image('thumb.jpg')->size(Video::THUMB_FILE_MAX_SIZE + 10);
        $data = [
            'thumb_file' => $file
        ];
        $this->assertInvalidationInStoreAction($data, 'max.file', ['max' => Video::THUMB_FILE_MAX_SIZE]);
        $this->assertInvalidationInUpdateAction($data, 'max.file', ['max' => Video::THUMB_FILE_MAX_SIZE]);

        $file = UploadedFile::fake()->create('thumb.mp4')->size(Video::THUMB_FILE_MAX_SIZE + 10);
        $data = [
            'thumb_file' => $file
        ];
        $this->assertInvalidationInStoreAction($data, 'image');
        $this->assertInvalidationInUpdateAction($data, 'image');
    }

    public function testInvalidationBannerField()
    {
        $file = UploadedFile::fake()->image('thumb.jpg')->size(Video::BANNER_FILE_MAX_SIZE + 10);
        $data = [
            'banner_file' => $file
        ];
        $this->assertInvalidationInStoreAction($data, 'max.file', ['max' => Video::BANNER_FILE_MAX_SIZE]);
        $this->assertInvalidationInUpdateAction($data, 'max.file', ['max' => Video::BANNER_FILE_MAX_SIZE]);

        $file = UploadedFile::fake()->create('thumb.mp4')->size(Video::BANNER_FILE_MAX_SIZE + 10);
        $data = [
            'banner_file' => $file
        ];
        $this->assertInvalidationInStoreAction($data, 'image');
        $this->assertInvalidationInUpdateAction($data, 'image');
    }

    public function testInvalidationTrailerFileField()
    {
        $file = UploadedFile::fake()->create('video.mp4')->size(Video::TRAILER_FILE_MAX_SIZE + 10);
        $data = [
            'trailer_file' => $file
        ];
        $this->assertInvalidationInStoreAction($data, 'max.file', ['max' => Video::TRAILER_FILE_MAX_SIZE]);
        $this->assertInvalidationInUpdateAction($data, 'max.file', ['max' => Video::TRAILER_FILE_MAX_SIZE]);

        $file = UploadedFile::fake()->create('video.mkv');
        $data = [
            'trailer_file' => $file
        ];
        $this->assertInvalidationInStoreAction($data, 'mimetypes', ['values' => 'video/mp4']);
        $this->assertInvalidationInUpdateAction($data, 'mimetypes', ['values' => 'video/mp4']);
    }

    public function testInvalidationVideoFileField()
    {
        $file = UploadedFile::fake()->create('video.mp4')->size(Video::VIDEO_FILE_MAX_SIZE + 10);
        $data = [
            'video_file' => $file
        ];
        $this->assertInvalidationInStoreAction($data, 'max.file', ['max' => Video::VIDEO_FILE_MAX_SIZE]);
        $this->assertInvalidationInUpdateAction($data, 'max.file', ['max' => Video::VIDEO_FILE_MAX_SIZE]);

        $file = UploadedFile::fake()->create('video.mkv');
        $data = [
            'video_file' => $file
        ];
        $this->assertInvalidationInStoreAction($data, 'mimetypes', ['values' => 'video/mp4']);
        $this->assertInvalidationInUpdateAction($data, 'mimetypes', ['values' => 'video/mp4']);
    }

    public function testSaveWithFileUpload()
    {
        $files = $this->getFiles();
        $data = $this->sendData + $files;

        $response = $this->json('POST', $this->routeStore(), $data);

        $response->assertStatus(201);

        foreach ($files as $file) {
            \Storage::assertExists($response->json('data.id') . '/' . $file->hashName());
        }
    }

    public function testUpdateWithFileUpload()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $data = $this->sendData + $files;

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $data
        );

        $response->assertStatus(200);
        $id = $response->json('data.id');

        foreach ($files as $file) {
            \Storage::assertExists($id . '/' . $file->hashName());
        }

        $newFiles = [
            'thumb_file' => UploadedFile::fake()->create('thumb2.jpg'),
            'video_file' => UploadedFile::fake()->create('video2.mp4')
        ];

        $data = $this->sendData + $newFiles;
        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $data
        );
        $response->assertStatus(200);

        \Storage::assertMissing("{$id}/{$files['thumb_file']->hashName()}");
        \Storage::assertMissing("{$id}/{$files['video_file']->hashName()}");
        \Storage::assertExists("{$id}/{$newFiles['thumb_file']->hashName()}");
        \Storage::assertExists("{$id}/{$newFiles['video_file']->hashName()}");

    }

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create('video_file.mp4'),
            'thumb_file' => UploadedFile::fake()->image('thumb_file.jpg'),
            'banner_file' => UploadedFile::fake()->image('banner.jpg'),
            'trailer_file' => UploadedFile::fake()->create('trailer.mp4')
        ];
    }
}
