<?php

namespace App\Support;

use App\Models\Gallery;

class ExifHelper
{
    public static function filterForGallery(Gallery $gallery, array $exif): array
    {
        $visibility = $gallery->exif_visibility ?? 'all';

        if ($visibility === 'none') {
            return [];
        }

        if ($visibility === 'custom') {
            $fields = is_array($gallery->exif_fields) ? $gallery->exif_fields : [];
            return self::pickMapped($exif, $fields);
        }

        // visibility === 'all'
        return $exif;
    }

    public static function pickMapped(array $exif, array $fields): array
    {
        $map = config('photos.exif_map', []);
        $out = [];
        foreach ($fields as $key) {
            if (!isset($map[$key])) continue;
            foreach ($map[$key] as $exifKey) {
                if (array_key_exists($exifKey, $exif)) {
                    $out[$key] = $exif[$exifKey];
                    break;
                }
            }
        }
        return $out;
    }
}

