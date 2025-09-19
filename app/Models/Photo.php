<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
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

    protected static function booted(): void
    {
        static::deleting(function (Photo $photo) {
            $photo->deleteFiles();
        });
    }

    public function deleteFiles(): void
    {
        foreach (['path_original', 'path_web', 'path_thumb'] as $attr) {
            $this->deleteFilePath($this->{$attr} ?? null);
        }
    }

    protected function deleteFilePath(?string $path): void
    {
        if (!$path) return;

        $candidates = [];
        $normalized = ltrim($path, '/');

        if (Str::startsWith($normalized, 'storage/app/')) {
            $rel = substr($normalized, strlen('storage/'));
            $candidates[] = storage_path($rel);
        }

        if (Str::startsWith($normalized, 'storage/')) {
            $candidates[] = public_path($normalized);
        }

        // Fallbacks
        $candidates[] = public_path($normalized);
        $candidates[] = base_path($normalized);

        foreach ($candidates as $full) {
            try {
                if ($full && File::exists($full)) {
                    File::delete($full);
                    // Do not break; attempt other candidates in case of duplicates
                }
            } catch (\Throwable $e) {
                // ignore filesystem errors
            }
        }
    }
}
