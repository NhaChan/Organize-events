<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['category_id', 'title', 'slug', 'summary', 'content', 'thumbnail', 'event_date', 'location', 'status', 'view_count', 'meta_title', 'meta_description'];

    protected $casts = ['event_date' => 'datetime', 'view_count' => 'integer'];

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
