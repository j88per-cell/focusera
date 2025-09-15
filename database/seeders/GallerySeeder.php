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
            'IMG_0002.CR2',
            'IMG_0003.CR2',
            'IMG_0004.CR2',
            'IMG_0006.CR2',
            'IMG_0007.CR2',
            'IMG_0008.CR2',
            'IMG_0012.CR2',
            'IMG_0023.CR2',
            'IMG_0024.CR2',
            'IMG_0025.CR2',
            'IMG_0026.CR2',
        ];

        foreach ($files as $file) {
            $jpg = preg_replace('/\.CR2$/i', '.jpg', $file);

            Photo::create([
                'gallery_id'  => $gallery->id,
                'title'       => pathinfo($jpg, PATHINFO_FILENAME),
                'description' => null,
                'path'        => "galleries/colorado/$jpg",
                'exif'        => json_encode([]), // can parse later
            ]);
        }
    }
}
