<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        'markup_percent',
        'attribution',
        'notes',
    ];

    protected $casts = [
        'exif' => 'array',
        'lat' => 'float',
        'long' => 'float',
        'markup_percent' => 'float',
    ];

    protected $appends = ['web_url', 'thumb_url'];

    public function gallery()
    {
        return $this->belongsTo(Gallery::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Photo $photo) {
            $photo->deleteFiles();
        });
    }

    public function deleteFiles(): void
    {
        $map = [
            'path_original' => config('site.storage.private_disk', 'photos_private'),
            'path_web' => config('site.storage.public_disk', 'photos_public'),
            'path_thumb' => config('site.storage.public_disk', 'photos_public'),
        ];

        foreach ($map as $attr => $disk) {
            $this->deleteFilePath($this->{$attr} ?? null, $disk);
        }
    }

    protected function deleteFilePath(?string $path, string $disk): void
    {
        if (!$path) return;
        $relative = $this->toRelativePath($path);
        try {
            if ($relative) {
                Storage::disk($disk)->delete($relative);
            }
        } catch (\Throwable $e) {
            // Ignore deletion errors
        }
    }

    public function getWebUrlAttribute(): ?string
    {
        return $this->resolvePublicUrl($this->path_web);
    }

    public function getThumbUrlAttribute(): ?string
    {
        return $this->resolvePublicUrl($this->path_thumb);
    }

    protected function resolvePublicUrl(?string $value): ?string
    {
        if (!$value) return null;
        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            return $value;
        }
        if (Str::startsWith($value, '/')) {
            return $value;
        }
        $relative = $this->toRelativePath($value);

        $disk = config('site.storage.public_disk', 'photos_public');
        try {
            return Storage::disk($disk)->url($relative ?? $value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    protected function toRelativePath(?string $value): ?string
    {
        if (!$value) return null;
        $value = ltrim($value);

        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            $base = config('site.storage.public_base_url');
            if ($base && Str::startsWith($value, $base)) {
                return ltrim(substr($value, strlen($base)), '/');
            }
            return null;
        }

        if (Str::startsWith($value, '/')) {
            $value = ltrim($value, '/');
        }

        if (Str::startsWith($value, 'storage/')) {
            return ltrim(substr($value, strlen('storage/')), '/');
        }

        return ltrim($value, '/');
    }
}
