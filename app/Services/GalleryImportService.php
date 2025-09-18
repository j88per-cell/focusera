<?php

namespace App\Services;

use App\Models\Gallery;
use App\Models\Photo;
use Illuminate\Support\Str;

class GalleryImportService
{
    /**
     * Import galleries from subdirectories of the given base directory.
     * Each immediate subfolder becomes a Gallery, each image becomes a Photo.
     */
    public function import(string $baseDir): array
    {
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR);
        if (!is_dir($baseDir)) {
            return ['galleries' => 0, 'photos' => 0];
        }

        $galleryCount = 0;
        $photoCount = 0;

        $dirs = array_values(array_filter(scandir($baseDir), function ($d) use ($baseDir) {
            return $d !== '.' && $d !== '..' && is_dir($baseDir . DIRECTORY_SEPARATOR . $d);
        }));

        foreach ($dirs as $folder) {
            $folderPath = $baseDir . DIRECTORY_SEPARATOR . $folder;
            $files = $this->imageFiles($folderPath);
            if (empty($files)) {
                continue;
            }

            // Create or fetch gallery
            $title = Str::title(str_replace(['-', '_'], ' ', $folder));

            $thumbnailPublic = $this->publicPath($folder, $files[0]);

            $gallery = Gallery::firstOrCreate(
                ['title' => $title],
                [
                    'description' => null,
                    'date'        => now(),
                    'public'      => true,
                    'thumbnail'   => $thumbnailPublic,
                ]
            );

            $galleryCount += $gallery->wasRecentlyCreated ? 1 : 0;

            // Import photos
            foreach ($files as $file) {
                $originalFull = $folderPath . DIRECTORY_SEPARATOR . $file;
                $pathOriginal = $this->originalPath($folder, $file); // stored for reference
                $pathWeb      = $this->publicPath($folder, $file);
                $pathThumb    = $this->publicPath($folder, $file);

                $exif = $this->readExif($originalFull);

                $exists = Photo::where('gallery_id', $gallery->id)
                    ->where('path_web', $pathWeb)
                    ->exists();
                if ($exists) {
                    continue;
                }

                Photo::create([
                    'gallery_id'   => $gallery->id,
                    'title'        => pathinfo($file, PATHINFO_FILENAME),
                    'description'  => null,
                    'path_original'=> $pathOriginal,
                    'path_web'     => $pathWeb,
                    'path_thumb'   => $pathThumb,
                    'exif'         => json_encode($exif),
                ]);

                $photoCount++;
            }
        }

        return ['galleries' => $galleryCount, 'photos' => $photoCount];
    }

    protected function imageFiles(string $folderPath): array
    {
        if (!is_dir($folderPath)) return [];
        $all = array_diff(scandir($folderPath), ['.', '..']);
        $files = array_values(array_filter($all, function ($f) use ($folderPath) {
            $full = $folderPath . DIRECTORY_SEPARATOR . $f;
            if (!is_file($full)) return false;
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif']);
        }));
        natsort($files);
        return array_values($files);
    }

    protected function readExif(string $fullPath): array
    {
        $exif = [];
        if (function_exists('exif_read_data')) {
            try {
                $data = @exif_read_data($fullPath, null, true, false);
                if (is_array($data)) {
                    $exif = $data;
                }
            } catch (\Throwable $e) {
                // ignore EXIF errors
            }
        }
        return $exif;
    }

    protected function publicPath(string $folder, string $file): string
    {
        return 'storage/' . trim($folder, '/\\') . '/' . $file;
    }

    protected function originalPath(string $folder, string $file): string
    {
        // Reference path to original stored under storage/app/private
        return 'storage/app/private/' . trim($folder, '/\\') . '/' . $file;
    }
}

