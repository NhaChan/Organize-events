<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryPage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'service_image_position_y' => 'integer',
        'banner_image_position_y' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function contentBlocks()
    {
        return $this->hasMany(CategoryContentBlock::class)->orderBy('sort_order')->orderBy('id');
    }

    public function galleryImages()
    {
        return $this->hasMany(CategoryPageImage::class)->orderBy('sort_order')->orderBy('id');
    }
}
