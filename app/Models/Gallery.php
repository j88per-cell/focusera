<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'public',
        'access_code',
        'thumbnail',
    ];

    protected $casts = [
        'public' => 'boolean',
        'date' => 'datetime',
    ];

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }
}
