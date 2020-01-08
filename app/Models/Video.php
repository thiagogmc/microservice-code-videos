<?php

namespace App\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes, Uuid, UploadFiles;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    const THUMB_FILE_MAX_SIZE = 1024 * 5; //5 mb
    const BANNER_FILE_MAX_SIZE = 1024 * 10; //10 mb
    const VIDEO_FILE_MAX_SIZE = 1024 * 1024 * 1; // 1 gb
    const TRAILER_FILE_MAX_SIZE = 1024 * 1024 * 50; // 50gb


    protected $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
        'video_file',
        'thumb_file',
        'banner_file',
        'trailer_file'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'id' => 'string',
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration' => 'integer',
    ];

    public $incrementing = false;
    public static $fileFields = ['video_file', 'thumb_file', 'banner_file', 'trailer_file'];

    protected $appends = [
        'video_file_url',
        'thumb_file_url',
        'banner_file_url',
        'trailer_file_url'
    ];

    public static function create(array $attributes = [])
    {
        $files = self::extractFiles($attributes);
        try {
            \DB::beginTransaction();
            $obj = static::query()->create($attributes);
            static::handleRelations($obj, $attributes);
            $obj->uploadFiles($files);
            \DB::commit();

            return $obj;
        } catch (\Exception $e) {
            if (isset($obj)) {
                $obj->deleteFiles($files);
            }
            \DB::rollBack();
            throw $e; //Subindo a Exception até ela ser jogada na tela.
        }
    }

    public function update(array $attributes = [], array $options = [])
    {
        $files = self::extractFiles($attributes);
        try {
            \DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            if ($saved) {
                $this->uploadFiles($files);
            }
            \DB::commit();
            if ($saved && count($files)) {
                $this->deleteOldFiles();
            }
            return $saved;
        } catch (\Exception $e) {
            $this->deleteFiles($files);
            \DB::rollBack();
            throw $e; //Subindo a Exception até ela ser jogada na tela.
        }
    }


    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }
    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }

    public static function handleRelations(Video $video, array $attributes)
    {
        if (isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }

        if (isset($attributes['genres_id'])) {
            $video->genres()->sync($attributes['genres_id']);
        }
    }

    protected function uploadDir()
    {
        return $this->id;
    }

    public function getVideoFileUrlAttribute()
    {
        return $this->video_file ? $this->getPublicLink($this->video_file) : null;
    }

    public function getThumbFileUrlAttribute()
    {
        return $this->thumb_file ? $this->getPublicLink($this->thumb_file) : null;
    }

    public function getBannerFileUrlAttribute()
    {
        return $this->banner_file ? $this->getPublicLink($this->banner_file) : null;
    }

    public function getTrailerFileUrlAttribute()
    {
        return $this->trailer_file ? $this->getPublicLink($this->trailer_file) : null;
    }
}
