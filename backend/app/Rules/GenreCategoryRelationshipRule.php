<?php
declare(strict_types=1);
namespace App\Rules;

use App\Models\Genre;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;
use PhpParser\Node\Expr\Array_;

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
        $this->categoriesId = array_unique($categoriesId);
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
            $categoriesOfAGenre = $this->getCategoriesOfAGenre($genre);

            if (!count($categoriesOfAGenre)) {
                return false;
            }

            array_push($matchedCategories, ...$categoriesOfAGenre);
        }

        $matchedCategories = array_unique($matchedCategories);
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
        return trans('validation.genre_category_relationship');
    }

    protected function getCategoriesOfAGenre($id): Array
    {
        return Genre::find($id)
            ->categories()
            ->whereIn('category_id', $this->categoriesId)
            ->pluck('id')
            ->toArray();
    }
}
