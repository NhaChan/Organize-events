<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['parent_id', 'name', 'slug', 'description'];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function page()
    {
        return $this->hasOne(CategoryPage::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
