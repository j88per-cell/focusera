<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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

        $publicDisk = config('site.storage.public_disk', 'photos_public');
        $privateDisk = config('site.storage.private_disk', 'photos_private');

        $originalRelative = $this->originalRelativePath($galleryId, $destName);
        $webRelative = $this->webRelativePath($galleryId, $destName);
        $thumbRelative = $this->thumbRelativePath($galleryId, $destName);

        $this->storeOriginal($privateDisk, $originalRelative, $sourceFullPath);

        $derivatives = $this->makeDerivatives($sourceFullPath, $destName, $ext);

        $this->storeFile($publicDisk, $webRelative, $derivatives['web']);
        $this->storeFile($publicDisk, $thumbRelative, $derivatives['thumb']);

        $this->cleanupTemps($derivatives);

        return [
            'path_original' => $originalRelative,
            'path_web'      => $webRelative,
            'path_thumb'    => $thumbRelative,
        ];
    }

    protected function storeOriginal(string $disk, string $relative, string $sourceFullPath): void
    {
        $stream = fopen($sourceFullPath, 'rb');
        if (!$stream) {
            throw new \RuntimeException("Unable to read source file: {$sourceFullPath}");
        }
        Storage::disk($disk)->put($relative, $stream);
        fclose($stream);
    }

    protected function sanitizeFileName(string $name): string
    {
        $n = Str::slug(pathinfo($name, PATHINFO_FILENAME));
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        return $n . '.' . $ext;
    }

    protected function makeDerivatives(string $origFull, string $destName, string $ext): array
    {
        $webTmp = $this->makeTempFile('web_', $destName);
        $thumbTmp = $this->makeTempFile('thumb_', $destName);

        $webMax   = (int) config('photos.web_max_px', 800);
        $thumbMax = (int) config('photos.thumb_max_px', 400);

        if (function_exists('imagecreatefromjpeg')) {
            $img = $this->gdLoad($origFull, $ext);
            if (!$img) throw new \RuntimeException('Unsupported image format for GD.');

            $webImg = $this->gdResizeMax($img, $webMax);
            $this->gdSave($webImg, $webTmp, $ext);

            $thumbImg = $this->gdResizeMax($img, $thumbMax);
            $this->gdSave($thumbImg, $thumbTmp, $ext);

            if ($webImg && $webImg !== $img) imagedestroy($webImg);
            if ($thumbImg && $thumbImg !== $img) imagedestroy($thumbImg);
            imagedestroy($img);
            return ['web' => $webTmp, 'thumb' => $thumbTmp];
        }

        if (class_exists('Imagick')) {
            $im = new \Imagick($origFull);
            $webClone = clone $im;
            $this->imagickResizeMax($webClone, $webMax);
            $webClone->writeImage($webTmp);
            $webClone->clear();

            $thumbClone = clone $im;
            $this->imagickResizeMax($thumbClone, $thumbMax);
            $thumbClone->writeImage($thumbTmp);
            $thumbClone->clear();

            $im->clear();
            return ['web' => $webTmp, 'thumb' => $thumbTmp];
        }

        throw new \RuntimeException('No image library available (GD or Imagick required).');
    }

    protected function makeTempFile(string $prefix, string $destName): string
    {
        $tmp = tempnam(sys_get_temp_dir(), $prefix);
        if ($tmp === false) {
            throw new \RuntimeException('Unable to create temporary file.');
        }
        // ensure extension
        $ext = strtolower(pathinfo($destName, PATHINFO_EXTENSION));
        if ($ext) {
            $newTmp = $tmp . '.' . $ext;
            rename($tmp, $newTmp);
            $tmp = $newTmp;
        }
        return $tmp;
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

    protected function webRelativePath(int $galleryId, string $file): string
    {
        return 'gallery/web/' . $galleryId . '/' . $file;
    }

    protected function thumbRelativePath(int $galleryId, string $file): string
    {
        return 'gallery/thumbnails/' . $galleryId . '/' . $file;
    }

    protected function originalRelativePath(int $galleryId, string $file): string
    {
        return 'gallery/originals/' . $galleryId . '/' . $file;
    }

    protected function storeFile(string $disk, string $relative, string $tmpPath): void
    {
        $stream = fopen($tmpPath, 'rb');
        if (!$stream) {
            throw new \RuntimeException('Unable to open derivative for storage.');
        }
        Storage::disk($disk)->put($relative, $stream);
        fclose($stream);
    }

    protected function cleanupTemps(array $files): void
    {
        foreach ($files as $tmp) {
            if ($tmp && file_exists($tmp)) {
                @unlink($tmp);
            }
        }
    }
}
