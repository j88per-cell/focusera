<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Gallery extends Model
{
    use HasFactory, NodeTrait;

    protected $fillable = [
        'title',
        'description',
        'date',
        'public',
        'access_code',
        'thumbnail',
        'parent_id',
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
