<?php

namespace Tests\Unit;

use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'site.storage.public_disk' => 'photos_public',
            'site.storage.private_disk' => 'photos_private',
        ]);

        Storage::fake('photos_public');
        Storage::fake('photos_private');
    }

    public function test_resolves_public_urls_for_web_and_thumb_paths(): void
    {
        $photo = Photo::factory()->create([
            'path_web' => 'storage/gallery/web/sample.jpg',
            'path_thumb' => 'storage/gallery/thumbnails/sample.jpg',
        ]);

        $expectedWeb = Storage::disk('photos_public')->url('gallery/web/sample.jpg');
        $expectedThumb = Storage::disk('photos_public')->url('gallery/thumbnails/sample.jpg');

        $this->assertSame($expectedWeb, $photo->web_url);
        $this->assertSame($expectedThumb, $photo->thumb_url);
    }

    public function test_delete_files_removes_all_variants_from_storage(): void
    {
        $photo = Photo::factory()->create([
            'path_original' => 'storage/app/private/sample.jpg',
            'path_web' => 'storage/gallery/web/sample.jpg',
            'path_thumb' => 'storage/gallery/thumbnails/sample.jpg',
        ]);

        Storage::disk('photos_private')->put('app/private/sample.jpg', 'original');
        Storage::disk('photos_public')->put('gallery/web/sample.jpg', 'web');
        Storage::disk('photos_public')->put('gallery/thumbnails/sample.jpg', 'thumb');

        $photo->deleteFiles();

        Storage::disk('photos_private')->assertMissing('app/private/sample.jpg');
        Storage::disk('photos_public')->assertMissing('gallery/web/sample.jpg');
        Storage::disk('photos_public')->assertMissing('gallery/thumbnails/sample.jpg');
    }
}
