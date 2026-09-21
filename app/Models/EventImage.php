<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventImage extends Model
{
    public $timestamps = false;

    protected $fillable = ['event_id', 'image_path', 'title', 'content', 'alt_text', 'display_fit', 'position_y', 'sort_order'];

    protected $casts = ['position_y' => 'integer'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
