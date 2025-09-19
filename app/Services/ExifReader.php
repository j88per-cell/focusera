<?php

namespace App\Services;

class ExifReader
{
    public function read(string $fullPath): array
    {
        $exif = [];
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if (function_exists('exif_read_data') && in_array($ext, ['jpg','jpeg','tif','tiff'])) {
            try {
                $data = @exif_read_data($fullPath, null, true, false);
                if (is_array($data)) { $exif = $this->flatten($data); }
            } catch (\Throwable $e) { /* ignore */ }
        }

        if (empty($exif) && $this->exiftoolAvailable()) {
            try {
                $json = @shell_exec('exiftool -json -fast2 ' . escapeshellarg($fullPath) . ' 2>/dev/null');
                $decoded = json_decode($json, true);
                if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
                    $exif = $decoded[0];
                }
            } catch (\Throwable $e) { /* ignore */ }
        }

        return is_array($exif) ? $exif : [];
    }

    public function pickDate(array $exif): ?string
    {
        $candidates = [
            'DateTimeOriginal', 'CreateDate', 'DateCreated', 'DateTime',
            'EXIF\DateTimeOriginal', 'QuickTime\CreateDate', 'Composite\SubSecDateTimeOriginal'
        ];
        foreach ($candidates as $key) {
            if (isset($exif[$key]) && is_string($exif[$key]) && trim($exif[$key]) !== '') {
                $ts = strtotime($exif[$key]);
                if ($ts) return date('Y-m-d', $ts);
            }
        }
        return null;
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

    protected function flatten(array $arr): array
    {
        // exif_read_data returns nested sections; flatten one level with Section\Key names
        $out = [];
        foreach ($arr as $section => $values) {
            if (!is_array($values)) continue;
            foreach ($values as $k => $v) {
                $key = is_string($section) ? ($section . '\\' . $k) : $k;
                $out[$key] = $v;
            }
        }
        return $out;
    }
}

