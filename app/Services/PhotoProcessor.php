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

        // Derivatives (public): resize to configured sizes
        $webFull   = $dirs['web'] . DIRECTORY_SEPARATOR . $destName;
        $thumbFull = $dirs['thumbs'] . DIRECTORY_SEPARATOR . $destName;
        $this->makeDerivatives($origFull, $webFull, $thumbFull, $ext);

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

    protected function makeDerivatives(string $origFull, string $webFull, string $thumbFull, string $ext): void
    {
        File::ensureDirectoryExists(dirname($webFull), 0755, true);
        File::ensureDirectoryExists(dirname($thumbFull), 0755, true);

        $webMax   = (int) config('photos.web_max_px', 800);
        $thumbMax = (int) config('photos.thumb_max_px', 400);

        if (function_exists('imagecreatefromjpeg')) {
            $img = $this->gdLoad($origFull, $ext);
            if (!$img) throw new \RuntimeException('Unsupported image format for GD.');

            $webImg = $this->gdResizeMax($img, $webMax);
            $this->gdSave($webImg, $webFull, $ext);

            $thumbImg = $this->gdResizeMax($img, $thumbMax);
            $this->gdSave($thumbImg, $thumbFull, $ext);

            if ($webImg && $webImg !== $img) imagedestroy($webImg);
            if ($thumbImg && $thumbImg !== $img) imagedestroy($thumbImg);
            imagedestroy($img);
            return;
        }

        if (class_exists('Imagick')) {
            $im = new \Imagick($origFull);
            $webClone = clone $im;
            $this->imagickResizeMax($webClone, $webMax);
            $webClone->writeImage($webFull);
            $webClone->clear();

            $thumbClone = clone $im;
            $this->imagickResizeMax($thumbClone, $thumbMax);
            $thumbClone->writeImage($thumbFull);
            $thumbClone->clear();

            $im->clear();
            return;
        }

        throw new \RuntimeException('No image library available (GD or Imagick required).');
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

    protected function gdResizeMax($img, int $max)
    {
        $w = imagesx($img); $h = imagesy($img);
        $scale = max($w, $h) > $max ? ($max / max($w, $h)) : 1.0;
        if ($scale >= 1.0) return $img;
        $nw = (int)round($w * $scale); $nh = (int)round($h * $scale);
        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
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
