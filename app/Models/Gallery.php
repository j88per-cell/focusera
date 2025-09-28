<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\NodeTrait;

class Gallery extends Model
{
    use HasFactory, NodeTrait;

    protected $fillable = [
        'title',
        'description',
        'attribution',
        'notes',
        'date',
        'public',
        'featured',
        'allow_orders',
        'markup_percent',
        'access_code',
        'thumbnail',
        'parent_id',
        'exif_visibility',
        'exif_fields',
    ];

    protected $casts = [
        'public' => 'boolean',
        'featured' => 'boolean',
        'allow_orders' => 'boolean',
        'date' => 'datetime',
        'exif_fields' => 'array',
        'markup_percent' => 'float',
    ];

    protected $appends = ['thumbnail_url'];

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function accessCodes()
    {
        return $this->hasMany(\App\Models\GalleryAccessCode::class);
    }

    protected static function booted(): void
    {
        // Ensure photo model events fire so files are removed on gallery deletion
        static::deleting(function (Gallery $gallery) {
            $gallery->photos()->get()->each->delete();
        });
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $value = $this->thumbnail;
        if (!$value) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            return $value;
        }

        if (Str::startsWith($value, '/')) {
            return $value;
        }

        if (Str::startsWith($value, 'storage/')) {
            return '/' . ltrim($value, '/');
        }

        try {
            return Storage::disk(config('site.storage.public_disk', 'photos_public'))->url($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
