<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Support\BotGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoMediaController extends Controller
{
    public function web(Request $request, Photo $photo)
    {
        $enabled = $this->boolConfig('site.photoproxy')
            ?? $this->boolConfig('settings.site.photoproxy');

        if (!$enabled) {
            abort(404);
        }

        $photo->loadMissing('gallery');
        $gallery = $photo->gallery;
        if (!$gallery) {
            abort(404);
        }

        $this->authorize('view', $gallery);

        if (BotGuard::shouldBlock($request)) {
            abort(404);
        }

        $publicDisk = config('site.storage.public_disk', 'photos_public');
        $relative = $this->relativePath($photo->path_web);
        if (!$relative) {
            abort(404);
        }

        try {
            $stream = Storage::disk($publicDisk)->readStream($relative);
            if (!$stream) {
                abort(404);
            }
        } catch (\Throwable $e) {
            abort(404);
        }

        $meta = $this->streamMeta($publicDisk, $relative);

        return Response::stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) fclose($stream);
        }, 200, array_filter([
            'Content-Type' => $meta['mime'] ?? 'image/jpeg',
            'Content-Length' => $meta['size'] ?? null,
            'Cache-Control' => 'private, max-age=3600',
            'Content-Disposition' => 'inline; filename="' . basename($relative) . '"',
        ]));
    }

    protected function relativePath(?string $value): ?string
    {
        if (!$value) return null;

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

    protected function boolConfig(string $key): ?bool
    {
        $value = config($key);
        if (is_null($value)) {
            return null;
        }
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (int) $value === 1;
        }
        if (is_string($value)) {
            $lower = strtolower($value);
            return in_array($lower, ['1', 'true', 'yes', 'on'], true);
        }
        return (bool) $value;
    }

    protected function streamMeta(string $disk, string $path): array
    {
        try {
            $mime = Storage::disk($disk)->mimeType($path);
        } catch (\Throwable $e) {
            $mime = null;
        }

        try {
            $size = Storage::disk($disk)->size($path);
        } catch (\Throwable $e) {
            $size = null;
        }

        return ['mime' => $mime, 'size' => $size];
    }
}
