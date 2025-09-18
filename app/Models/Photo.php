<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'gallery_id',
        'title',
        'description',
        'exif',
        'lat',
        'long',
        'path_original',
        'path_web',
        'path_thumb',
    ];

    protected $casts = [
        'exif' => 'array',
        'lat' => 'float',
        'long' => 'float',
    ];

    public function gallery()
    {
        return $this->belongsTo(Gallery::class);
    }
}
