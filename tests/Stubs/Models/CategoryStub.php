<?php

namespace Tests\Stubs\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;

class CategoryStub extends Model
{
    protected $table = 'categories_stubs';
    protected $fillable = ['name', 'description'];

    public static function createTable()
    {
        \Schema::create('categories_stubs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public static function dropTable()
    {
        \Schema::dropIfExists('categories_stubs');
    }

}
