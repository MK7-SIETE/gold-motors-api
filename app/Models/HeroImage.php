<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroImage extends Model
{
    protected $fillable = ['filename', 'original_name', 'sort_order'];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return url('uploads/heroes/' . $this->filename);
    }
}