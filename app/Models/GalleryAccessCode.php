<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryAccessCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'gallery_id',
        'code_hash',
        'label',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function gallery()
    {
        return $this->belongsTo(Gallery::class);
    }
}

