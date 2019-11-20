<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{
    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean',
        'categories_id' => 'required|array|exists:categories,id',
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

        return $genre;
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

        return $genre;
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


}
