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
            'title'       => 'Colorado',
            'description' => 'Nature',
            'date'        => now(),
            'public'      => true,
            'thumbnail'   => 'storage/colorado/IMG_0007.jpg',
        ]);

        $files = array_diff(scandir(storage_path('app/public/colorado')), ['.', '..']);

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
