<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryPageImage extends Model
{
    protected $guarded = [];

    public function page()
    {
        return $this->belongsTo(CategoryPage::class, 'category_page_id');
    }
}
