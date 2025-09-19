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

        // visibility === 'all' — return normalized friendly fields too
        $allKeys = array_keys(config('photos.exif_map', []));
        // Also include derived GPS keys
        $allKeys = array_merge($allKeys, ['latitude', 'longitude']);
        return self::pickMapped($exif, $allKeys);
    }

    public static function pickMapped(array $exif, array $fields): array
    {
        $map = config('photos.exif_map', []);
        $out = [];
        foreach ($fields as $key) {
            if ($key === 'latitude' || $key === 'longitude') {
                $gps = self::extractGps($exif);
                if ($gps[$key] !== null) $out[$key] = $gps[$key];
                continue;
            }
            if (!isset($map[$key])) continue;
            foreach ($map[$key] as $exifKey) {
                if (array_key_exists($exifKey, $exif)) {
                    $val = $exif[$exifKey];
                    if ($key === 'datetime') {
                        $ts = is_string($val) ? strtotime($val) : null;
                        if ($ts) { $val = date('Y-m-d H:i', $ts); }
                    }
                    if ($key === 'photographer' && is_array($val)) {
                        $val = reset($val);
                    }
                    $out[$key] = $val;
                    break;
                }
            }
        }
        return $out;
    }

    protected static function extractGps(array $exif): array
    {
        $lat = self::readGpsCoord($exif, ['GPSLatitude', 'EXIF\\GPSLatitude'], ['GPSLatitudeRef', 'EXIF\\GPSLatitudeRef']);
        $lon = self::readGpsCoord($exif, ['GPSLongitude', 'EXIF\\GPSLongitude'], ['GPSLongitudeRef', 'EXIF\\GPSLongitudeRef']);
        return ['latitude' => $lat, 'longitude' => $lon];
    }

    protected static function readGpsCoord(array $exif, array $coordKeys, array $refKeys): ?float
    {
        $coord = null; $ref = null;
        foreach ($coordKeys as $k) { if (isset($exif[$k])) { $coord = $exif[$k]; break; } }
        foreach ($refKeys as $k) { if (isset($exif[$k])) { $ref = $exif[$k]; break; } }
        if ($coord === null) return null;
        try {
            if (is_array($coord)) {
                $deg = self::rationalToFloat($coord[0] ?? '0/1');
                $min = self::rationalToFloat($coord[1] ?? '0/1');
                $sec = self::rationalToFloat($coord[2] ?? '0/1');
                $val = $deg + ($min/60.0) + ($sec/3600.0);
            } else {
                $val = is_numeric($coord) ? floatval($coord) : floatval(str_replace([',','°'], ['.',''], (string)$coord));
            }
            if (is_string($ref)) $ref = strtoupper($ref);
            if ($ref === 'S' || $ref === 'W') $val *= -1;
            return $val;
        } catch (\Throwable $e) { return null; }
    }

    protected static function rationalToFloat($r): float
    {
        if (is_numeric($r)) return floatval($r);
        if (is_string($r) && str_contains($r, '/')) {
            [$n,$d] = array_map('floatval', explode('/', $r, 2));
            return $d != 0.0 ? ($n/$d) : 0.0;
        }
        return 0.0;
    }
}
