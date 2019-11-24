<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use App\Models\Video;
use App\Rules\GenreCategoryRelationshipRule;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genres_id' => [
                'required',
                'array',
                'exists:genres,id,deleted_at,NULL',
            ]
        ];
    }

    public function store(Request $request)
    {
        $this->addRuleGenreCategoryRelationshipRule($request);
        $validateData = $this->validate($request, $this->rulesStore());
        $self = $this;

        $video = \DB::transaction(function () use ($request, $validateData, $self) {
            $video = Video::create($validateData);
            $self->handleRelations($video, $request);
            return $video;
        });

        $video->refresh();

        return $video;
    }

    public function update(Request $request, $id)
    {
        $video = $this->findOrFail($id);
        $self = $this;
        $this->addRuleGenreCategoryRelationshipRule($request);
        $validatedData = $this->validate($request, $this->rulesUpdate());

        $video = \DB::transaction(function () use ($request, $validatedData, $self, $video) {
            $video->update($validatedData);
            $self->handleRelations($video, $request);

            return $video;
        });

        return $video;
    }

    protected function handleRelations($video, Request $request)
    {
        $video->categories()->sync($request->categories_id);
        $video->genres()->sync($request->genres_id);
    }

    protected function model()
    {
        return Video::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

    protected function addRuleGenreCategoryRelationshipRule(Request $request)
    {
        $categories_id = is_array($request->get('categories_id')) ? $request->get('categories_id') : [];
        $this->rules['genres_id'][] = new GenreCategoryRelationshipRule($categories_id);
    }
}
