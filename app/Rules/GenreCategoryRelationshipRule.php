<?php

namespace App\Rules;

use App\Models\Genre;
use Illuminate\Contracts\Validation\Rule;

class GenreCategoryRelationshipRule implements Rule
{
    private $categoriesId;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $categoriesId)
    {
        $this->categoriesId = $categoriesId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (! is_array($value)) {
            return false;
        }

        if (!count($value) or !count($this->categoriesId)) {
            return false;
        }

        $matchedCategories = [];

        foreach ($value as $genre) {
            $categoriesOfAGenre = Genre::find($genre);
            $categoriesOfAGenre = $categoriesOfAGenre
                ->categories()
                ->whereIn('category_id', $this->categoriesId)
                ->pluck('id');

            if (!count($categoriesOfAGenre)) {
                return false;
            }

            $matchedCategories[] = $categoriesOfAGenre;
            $matchedCategories = array_unique($matchedCategories);
        }

        if (count($matchedCategories) != count($this->categoriesId)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'A genre must be related at least a category.';
    }
}
