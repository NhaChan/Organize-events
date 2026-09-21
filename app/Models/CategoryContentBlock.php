<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryContentBlock extends Model
{
    protected $fillable = ['heading', 'content', 'image', 'image_alt', 'image_caption', 'image_fit', 'image_position_y', 'after_content', 'sort_order'];

    protected $casts = ['sort_order' => 'integer', 'image_position_y' => 'integer'];

    public function page()
    {
        return $this->belongsTo(CategoryPage::class, 'category_page_id');
    }
}
