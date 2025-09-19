<?php

namespace App\Services;

use App\Models\Photo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PhotoTransformer
{
    public function transform(Photo $photo, array $ops = []): void
    {
        $origFull = $this->toFilesystemPath($photo->path_original);
        $webFull  = $this->toFilesystemPath($photo->path_web, true);
        $thumbFull= $this->toFilesystemPath($photo->path_thumb, true);

        if (!File::exists($origFull)) {
            throw new \RuntimeException('Original file not found: ' . $origFull);
        }

        $ext = strtolower(pathinfo($origFull, PATHINFO_EXTENSION));

        // Prefer GD; fallback to Imagick if available
        if (function_exists('imagecreatefromjpeg') || class_exists(\Imagick::class)) {
            $this->transformWithGdOrImagick($origFull, $webFull, $thumbFull, $ext, $ops);
            return;
        }

        throw new \RuntimeException('No image library available (GD or Imagick required).');
    }

    protected function transformWithGdOrImagick(string $orig, string $web, string $thumb, string $ext, array $ops): void
    {
        if (function_exists('imagecreatefromjpeg')) {
            $img = $this->gdLoad($orig, $ext);
            if (!$img) throw new \RuntimeException('Unsupported image format for GD.');

            // rotate
            $deg = (int)($ops['rotate'] ?? 0);
            if ($deg !== 0) {
                $img = imagerotate($img, -$deg, 0); // GD rotates counter-clockwise for positive degrees
            }
            // flip
            $flip = $ops['flip'] ?? null; // 'h' | 'v'
            if ($flip && function_exists('imageflip')) {
                imageflip($img, $flip === 'h' ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
            } elseif ($flip) {
                // simple manual flip fallback for horizontal
                $img = $this->gdManualFlip($img, $flip);
            }

            // Ensure destination dirs exist
            File::ensureDirectoryExists(dirname($web), 0755, true);
            File::ensureDirectoryExists(dirname($thumb), 0755, true);

            // Save web (original size)
            $this->gdSave($img, $web, $ext);

            // Save thumbnail (max 400px longest edge)
            $thumbImg = $this->gdResizeMax($img, 400);
            $this->gdSave($thumbImg, $thumb, $ext);

            imagedestroy($img);
            if ($thumbImg && $thumbImg !== $img) imagedestroy($thumbImg);
            return;
        }

        // Imagick fallback
        /** @var \Imagick $im */
        $im = new \Imagick($orig);
        $deg = (int)($ops['rotate'] ?? 0);
        if ($deg !== 0) { $im->rotateImage(new \ImagickPixel('none'), $deg); }
        $flip = $ops['flip'] ?? null;
        if ($flip === 'h') { $im->flopImage(); }
        if ($flip === 'v') { $im->flipImage(); }

        File::ensureDirectoryExists(dirname($web), 0755, true);
        File::ensureDirectoryExists(dirname($thumb), 0755, true);

        $im->writeImage($web);

        $thumbClone = clone $im;
        $this->imagickResizeMax($thumbClone, 400);
        $thumbClone->writeImage($thumb);
        $thumbClone->clear();
        $im->clear();
    }

    protected function gdLoad(string $path, string $ext)
    {
        return match ($ext) {
            'jpg', 'jpeg' => imagecreatefromjpeg($path),
            'png' => imagecreatefrompng($path),
            default => null,
        };
    }

    protected function gdSave($img, string $path, string $ext): void
    {
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($img, $path, 85);
                break;
            case 'png':
                imagepng($img, $path, 6);
                break;
            default:
                imagejpeg($img, $path, 85);
        }
    }

    protected function gdResizeMax($img, int $max): mixed
    {
        $w = imagesx($img); $h = imagesy($img);
        $scale = max($w, $h) > $max ? ($max / max($w, $h)) : 1.0;
        if ($scale >= 1.0) return $img;
        $nw = (int)round($w * $scale); $nh = (int)round($h * $scale);
        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        return $dst;
    }

    protected function gdManualFlip($img, string $dir)
    {
        $w = imagesx($img); $h = imagesy($img);
        $dst = imagecreatetruecolor($w, $h);
        if ($dir === 'h') {
            for ($x = 0; $x < $w; $x++) {
                imagecopy($dst, $img, $w - $x - 1, 0, $x, 0, 1, $h);
            }
        } else {
            for ($y = 0; $y < $h; $y++) {
                imagecopy($dst, $img, 0, $h - $y - 1, 0, $y, $w, 1);
            }
        }
        return $dst;
    }

    protected function imagickResizeMax(\Imagick $im, int $max): void
    {
        $w = $im->getImageWidth();
        $h = $im->getImageHeight();
        $scale = max($w, $h) > $max ? ($max / max($w, $h)) : 1.0;
        if ($scale < 1.0) {
            $im->resizeImage((int)round($w * $scale), (int)round($h * $scale), \Imagick::FILTER_LANCZOS, 1);
        }
    }

    protected function toFilesystemPath(string $relative, bool $forPublic = false): string
    {
        $rel = ltrim($relative, '/');
        if (Str::startsWith($rel, 'storage/app/')) {
            // Map to storage_path('app/...')
            $suffix = substr($rel, strlen('storage/'));
            return storage_path($suffix);
        }
        if (Str::startsWith($rel, 'storage/')) {
            // Public symlinked path -> storage/app/public
            $suffix = substr($rel, strlen('storage/'));
            return storage_path('app/' . ($forPublic ? 'public/' : '') . $suffix);
        }
        // Fallback: treat as public path
        return public_path($rel);
    }
}

