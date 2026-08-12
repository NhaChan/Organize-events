<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryPage extends Model
{
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function contentBlocks()
    {
        return $this->hasMany(CategoryContentBlock::class)->orderBy('sort_order')->orderBy('id');
    }
}
