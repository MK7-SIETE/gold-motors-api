<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CarImage extends Model
{
    protected $fillable = ['car_id', 'path', 'sort_order'];

    public $timestamps = true;

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function getUrlAttribute(): string
    {
        // If path is already a full URL (e.g. Unsplash or external), return as-is
        if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
            return $this->path;
        }

        // Otherwise serve from local storage
        return Storage::disk('public')->url($this->path);
    }

    protected $appends = ['url'];
    protected $hidden  = ['path'];
}
