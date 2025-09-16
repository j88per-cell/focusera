<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gallery;
use App\Models\Photo;

class GallerySeeder extends Seeder
{
    public function run()
    {
        $gallery = Gallery::create([
            'title'       => 'Colorado Nature',
            'description' => 'Sample gallery',
            'date'        => now(),
            'public'      => true,
            'thumbnail'   => 'galleries/colorado/IMG_0002.jpg',
        ]);

        $files = [
            'IMG_0002.jpg',
            'IMG_0003.jpg',
            'IMG_0004.jpg',
            'IMG_0006.jpg',
            'IMG_0007.jpg',
            'IMG_0008.jpg',
            'IMG_0012.jpg',
            'IMG_0023.jpg',
            'IMG_0024.jpg',
            'IMG_0025.jpg',
            'IMG_0026.jpg',
        ];

        foreach ($files as $file) {
            $jpg = preg_replace('/\.jpg$/i', '.jpg', $file);
            Photo::create([
                'gallery_id'  => $gallery->id,
                'title'       => pathinfo($jpg, PATHINFO_FILENAME),
                'description' => null,
                'path_original'    => "storage/app/private/colorado/$jpg",
                'path_web' => "storage/colorado/$jpg",
                'path_thumb' => "storage/colorado/$jpg",
                'exif'        => json_encode([]), // can parse later
            ]);
        }
    }
}
