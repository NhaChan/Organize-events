<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['category_id', 'title', 'slug', 'summary', 'content', 'after_gallery_title', 'after_gallery_content', 'thumbnail', 'thumbnail_alt', 'event_date', 'location', 'original_price', 'sale_price', 'status', 'view_count', 'meta_title', 'meta_description'];

    protected $casts = ['event_date' => 'datetime', 'view_count' => 'integer', 'original_price' => 'integer', 'sale_price' => 'integer'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(EventImage::class)->orderBy('sort_order');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
