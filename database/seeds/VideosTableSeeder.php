<?php

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class VideosTableSeeder extends Seeder
{
    private $allGenres;
    private $relations = [
        'genres_id' => [],
        'categories_id' => []
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dir = Storage::getDriver()->getAdapter()->getPathPrefix();
        File::deleteDirectory($dir, true);

        $self = $this;
        $this->allGenres = Genre::all();

        Model::reguard();
        factory(Video::class, 50)
            ->make()
            ->each(function (Video $video) use ($self) {
                $self->fetchRelations();
                Video::create(
                    array_merge(
                        $video->toArray(),
                        [
                            'thumb_file' => $self->getImageFile(),
                            'banner_file' => $self->getImageFile(),
                            'video_file' => $self->getVideoFile(),
                            'trailer_file' => $self->getVideoFile(),
                        ]
                    )
                );
            });

        Model::unguard();
    }

    public function fetchRelations()
    {
        $subGenres = $this->allGenres->random(5)->load('categories');
        $categoriesId = [];
        foreach ($subGenres as $genre) {
            array_push($categoriesId, ...$genre->categories->pluck('id')->toArray());
        }
        $categoriesId = array_unique($categoriesId);
        $genresId = $subGenres->pluck('id')->toArray();
        $this->relations['categories_id'] = $categoriesId;
        $this->relations['genres_id'] = $genresId;
    }

    public function getImageFile()
    {
        return new \Illuminate\Http\UploadedFile(
            storage_path('faker/thumbs/laravel.png'),
            'laravel.png'
        );
    }

    public function getVideoFile()
    {
        return new \Illuminate\Http\UploadedFile(
            storage_path('faker/videos/laravel.mp4'),
            'laravel.mp4'
        );
    }
}
