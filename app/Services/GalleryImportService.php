<?php

namespace App\Services;

use App\Models\Gallery;
use App\Models\Photo;
use Illuminate\Support\Str;
use App\Services\PhotoProcessor;

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

            $firstOriginalFull = $folderPath . DIRECTORY_SEPARATOR . $files[0];
            $firstExif = $this->readExif($firstOriginalFull);
            $dateCandidate = $this->chooseDateFromExif($firstExif);

            $gallery = Gallery::firstOrCreate(
                ['title' => $title],
                [
                    'description' => null,
                    'date'        => $dateCandidate ?: now(),
                    'public'      => true,
                    'thumbnail'   => null,
                ]
            );

            $galleryCount += $gallery->wasRecentlyCreated ? 1 : 0;

            // Import photos
            $processor = new PhotoProcessor();
            foreach ($files as $file) {
                $originalFull = $folderPath . DIRECTORY_SEPARATOR . $file;
                $paths = $processor->import($gallery->id, $originalFull, $file);

                $exif = $this->readExif($originalFull);

                $exists = Photo::where('gallery_id', $gallery->id)
                    ->where('path_web', $paths['path_web'])
                    ->exists();
                if ($exists) {
                    continue;
                }

                Photo::create([
                    'gallery_id'   => $gallery->id,
                    'title'        => pathinfo($file, PATHINFO_FILENAME),
                    'description'  => null,
                    'path_original'=> $paths['path_original'],
                    'path_web'     => $paths['path_web'],
                    'path_thumb'   => $paths['path_thumb'],
                    'exif'         => $exif,
                ]);
                $photoCount++;
            }

            // Set a thumbnail based on first imported photo
            if (!$gallery->thumbnail && !empty($files)) {
                $first = $processor->import($gallery->id, $folderPath . DIRECTORY_SEPARATOR . $files[0], $files[0]);
                $gallery->thumbnail = $first['path_thumb'];
                $gallery->save();
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
            return in_array($ext, config('photos.allowed_extensions', ['jpg','jpeg','png']));
        }));
        natsort($files);
        return array_values($files);
    }

    protected function readExif(string $fullPath): array
    {
        $exif = [];
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        // Try PHP's exif for JPEG/TIFF families
        if (function_exists('exif_read_data') && in_array($ext, ['jpg','jpeg','tif','tiff'])) {
            try {
                $data = @exif_read_data($fullPath, null, true, false);
                if (is_array($data)) {
                    $exif = $data;
                }
            } catch (\Throwable $e) {
                // ignore EXIF errors
            }
        }

        // For RAW like CR2 (or when PHP exif failed), try exiftool if available
        if (empty($exif) && $this->exiftoolAvailable()) {
            try {
                $json = @shell_exec('exiftool -json -fast2 ' . escapeshellarg($fullPath) . ' 2>/dev/null');
                $decoded = json_decode($json, true);
                if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
                    $exif = $decoded[0];
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return $exif;
    }

    protected function exiftoolAvailable(): bool
    {
        try {
            $which = @shell_exec('command -v exiftool 2>/dev/null');
            return is_string($which) && trim($which) !== '';
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function chooseDateFromExif(array $exif): ?string
    {
        // Common EXIF datetime keys across exif_read_data and exiftool
        $candidates = [
            'DateTimeOriginal', 'CreateDate', 'DateCreated', 'DateTime',
            'EXIF\DateTimeOriginal', 'QuickTime\CreateDate', 'Composite\SubSecDateTimeOriginal'
        ];
        foreach ($candidates as $key) {
            if (isset($exif[$key]) && is_string($exif[$key]) && trim($exif[$key]) !== '') {
                // Normalize to Y-m-d
                $ts = strtotime($exif[$key]);
                if ($ts) return date('Y-m-d', $ts);
            }
        }
        return null;
    }

    // path helpers are moved into PhotoProcessor
}
