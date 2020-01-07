<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{
    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
    ];

    public function store(Request $request)
    {
        $validateData = $this->validate($request, $this->rulesStore());
        $self = $this;

        $genre = \DB::transaction(function () use ($request, $validateData, $self) {
            $genre = Genre::create($validateData);
            $self->handleRelations($genre, $request);
            return $genre;
        });

        $genre->refresh();

        $resource = $this->resource();

        return new $resource($genre);
    }

    public function update(Request $request, $id)
    {
        $genre = $this->findOrFail($id);
        $validatedData = $this->validate($request, $this->rulesUpdate());

        $genre = \DB::transaction(function () use ($request, $validatedData, $genre) {
            $genre->update($validatedData);
            $this->handleRelations($genre, $request);

            return $genre;
        });

        $resource = $this->resource();

        return new $resource($genre);
    }

    protected function handleRelations($genre, Request $request)
    {
        $genre->categories()->sync($request->categories_id);
    }


    protected function model()
    {
        return Genre::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }


    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return GenreResource::class;
    }
}
