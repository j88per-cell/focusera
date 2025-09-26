<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
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

        $filePath = $this->resolveWebPath($photo);
        if (!$filePath || !is_file($filePath)) {
            abort(404);
        }

        $mime = mime_content_type($filePath) ?: 'image/jpeg';
        $size = filesize($filePath) ?: null;

        return Response::stream(function () use ($filePath) {
            $stream = fopen($filePath, 'rb');
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, array_filter([
            'Content-Type' => $mime,
            'Content-Length' => $size,
            'Cache-Control' => 'private, max-age=3600',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
        ]));
    }

    protected function resolveWebPath(Photo $photo): ?string
    {
        $candidates = [];
        $raw = $photo->path_web ?: $photo->path_original;
        if (!$raw) {
            return null;
        }

        $normalized = ltrim($raw, '/');

        if (Str::startsWith($normalized, 'storage/app/public/')) {
            $candidates[] = storage_path('app/public/' . substr($normalized, strlen('storage/app/public/')));
        }

        if (Str::startsWith($normalized, 'storage/')) {
            $candidates[] = public_path($normalized);
            $candidates[] = storage_path('app/public/' . substr($normalized, strlen('storage/')));
        }

        $candidates[] = storage_path('app/public/' . $normalized);
        $candidates[] = public_path($normalized);
        $candidates[] = base_path($normalized);

        foreach ($candidates as $candidate) {
            if ($candidate && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
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
}

