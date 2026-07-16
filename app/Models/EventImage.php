<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventImage extends Model
{
    public $timestamps = false;

    protected $fillable = ['event_id', 'image_path', 'alt_text', 'sort_order'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
