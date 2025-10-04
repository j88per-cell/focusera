<?php

namespace Tests\Unit;

use App\Models\Gallery;
use App\Support\ExifHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExifHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_empty_when_visibility_none(): void
    {
        $gallery = Gallery::factory()->create([
            'exif_visibility' => 'none',
        ]);

        $result = ExifHelper::filterForGallery($gallery, [
            'Model' => 'Canon EOS R5',
            'ISO' => 400,
        ]);

        $this->assertSame([], $result);
    }

    public function test_returns_all_mapped_fields_and_normalizes_values(): void
    {
        $gallery = Gallery::factory()->create([
            'exif_visibility' => 'all',
        ]);

        $exif = [
            'Model' => 'Nikon Z8',
            'LensModel' => 'NIKKOR Z 24-70mm',
            'ISO' => 640,
            'ExposureTime' => '1/200',
            'FNumber' => '2.8',
            'FocalLength' => '35.0 mm',
            'DateTimeOriginal' => '2024:05:01 12:34:56',
            'GPSLatitude' => ['37/1', '48/1', '3000/100'],
            'GPSLatitudeRef' => 'N',
            'GPSLongitude' => ['122/1', '16/1', '1200/100'],
            'GPSLongitudeRef' => 'W',
        ];

        $result = ExifHelper::filterForGallery($gallery, $exif);

        $this->assertSame('Nikon Z8', $result['camera']);
        $this->assertSame('NIKKOR Z 24-70mm', $result['lens']);
        $this->assertSame(640, $result['iso']);
        $this->assertSame('1/200', $result['shutter']);
        $this->assertSame('2.8', $result['aperture']);
        $this->assertSame('35.0 mm', $result['focal']);
        $this->assertSame('2024-05-01 12:34', $result['datetime']);
        $this->assertEqualsWithDelta(37.808333, $result['latitude'], 0.0001);
        $this->assertEqualsWithDelta(-122.27, $result['longitude'], 0.01);
    }

    public function test_returns_only_custom_fields_when_configured(): void
    {
        $gallery = Gallery::factory()->create([
            'exif_visibility' => 'custom',
            'exif_fields' => ['iso', 'shutter'],
        ]);

        $exif = [
            'Model' => 'Canon',
            'ISO' => 200,
            'ExposureTime' => '1/60',
            'FNumber' => '4.0',
        ];

        $result = ExifHelper::filterForGallery($gallery, $exif);

        $this->assertSame([
            'iso' => 200,
            'shutter' => '1/60',
        ], $result);
    }
}
