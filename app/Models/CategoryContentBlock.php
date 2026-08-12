<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryContentBlock extends Model
{
    protected $fillable = ['heading', 'content', 'image', 'image_alt', 'after_content', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    public function page()
    {
        return $this->belongsTo(CategoryPage::class, 'category_page_id');
    }
}
