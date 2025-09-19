<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PhotoProcessor
{
    public function import(int $galleryId, string $sourceFullPath, string $fileName): array
    {
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = config('photos.allowed_extensions', ['jpg','jpeg','png']);
        if (!in_array($ext, $allowed)) {
            throw new \InvalidArgumentException("Unsupported file extension: {$ext}");
        }

        $destName = $this->sanitizeFileName($fileName);

        $dirs = $this->ensureDirectories($galleryId);

        // Originals (private)
        $origFull = $dirs['originals'] . DIRECTORY_SEPARATOR . $destName;
        $this->copyIfChanged($sourceFullPath, $origFull);

        // Derivatives (public). For now, copy; future: resize/strip exif/watermark.
        $webFull   = $dirs['web'] . DIRECTORY_SEPARATOR . $destName;
        $thumbFull = $dirs['thumbs'] . DIRECTORY_SEPARATOR . $destName;
        $this->copyIfChanged($sourceFullPath, $webFull);
        $this->copyIfChanged($sourceFullPath, $thumbFull);

        // Return DB paths that map to public URLs via storage symlink
        return [
            'path_original' => $this->relativeOriginal($galleryId, $destName),
            'path_web'      => $this->relativeWeb($galleryId, $destName),
            'path_thumb'    => $this->relativeThumb($galleryId, $destName),
        ];
    }

    protected function ensureDirectories(int $galleryId): array
    {
        $origDir  = rtrim(storage_path('app/private/' . $galleryId), DIRECTORY_SEPARATOR);
        $webDir   = rtrim(storage_path('app/public/gallery/web/' . $galleryId), DIRECTORY_SEPARATOR);
        $thumbDir = rtrim(storage_path('app/public/gallery/thumbnails/' . $galleryId), DIRECTORY_SEPARATOR);

        File::ensureDirectoryExists($origDir, 0755, true);
        File::ensureDirectoryExists($webDir, 0755, true);
        File::ensureDirectoryExists($thumbDir, 0755, true);

        return [
            'originals' => $origDir,
            'web'       => $webDir,
            'thumbs'    => $thumbDir,
        ];
    }

    protected function sanitizeFileName(string $name): string
    {
        $n = Str::slug(pathinfo($name, PATHINFO_FILENAME));
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        return $n . '.' . $ext;
    }

    protected function copyIfChanged(string $src, string $dest): void
    {
        if (!File::exists($src)) {
            throw new \RuntimeException("Source file not found: {$src}");
        }
        if (!File::exists($dest) || File::size($src) !== File::size($dest)) {
            File::copy($src, $dest);
        }
    }

    protected function relativeWeb(int $galleryId, string $file): string
    {
        return 'storage/gallery/web/' . $galleryId . '/' . $file;
    }

    protected function relativeThumb(int $galleryId, string $file): string
    {
        return 'storage/gallery/thumbnails/' . $galleryId . '/' . $file;
    }

    protected function relativeOriginal(int $galleryId, string $file): string
    {
        return 'storage/app/private/' . $galleryId . '/' . $file;
    }
}

