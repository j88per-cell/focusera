<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\GalleryAccessCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class GalleryAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'site.analytics.enabled' => false,
            'inertia.testing.ensure_pages_exist' => false,
        ]);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->startSession();
    }

    public function test_guest_can_unlock_private_gallery_with_generated_code(): void
    {
        $gallery = Gallery::factory()->private()->create();
        $code = 'ACCESS123';
        $record = GalleryAccessCode::factory()->for($gallery)->withPlainCode($code)->create([
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->get(route('galleries.show', ['gallery' => $gallery, 'code' => $code]));
        $response->assertRedirect(route('galleries.show', $gallery));

        $flag = session('gallery.access.' . $gallery->id);
        $this->assertIsArray($flag);
        $this->assertSame($record->id, $flag['code_id']);

        $view = $this->get(route('galleries.show', $gallery));
        $view->assertOk()->assertInertia(function (AssertableInertia $page) {
            $page->component('Gallery/Show');
        });
    }

    public function test_access_is_denied_when_generated_code_is_expired(): void
    {
        $gallery = Gallery::factory()->private()->create();
        $code = 'EXPIRED42';
        GalleryAccessCode::factory()->for($gallery)->withPlainCode($code)->expired()->create();

        $response = $this->get(route('galleries.show', ['gallery' => $gallery, 'code' => $code]));

        $response->assertForbidden();
        $this->assertNull(session('gallery.access.' . $gallery->id));
    }

    public function test_legacy_plaintext_code_unlocks_gallery(): void
    {
        $code = 'LEGACY99';
        $gallery = Gallery::factory()->private()->withAccessCode($code)->create();

        $response = $this->get(route('galleries.show', ['gallery' => $gallery, 'code' => $code]));
        $response->assertRedirect(route('galleries.show', $gallery));

        $flag = session('gallery.access.' . $gallery->id);
        $this->assertTrue($flag);
    }

    public function test_access_submit_endpoint_redirects_to_gallery(): void
    {
        $gallery = Gallery::factory()->private()->create();
        $code = 'FORM1234';
        $record = GalleryAccessCode::factory()->for($gallery)->withPlainCode($code)->create([
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->post(route('galleries.access.submit'), ['code' => $code]);

        $response->assertRedirect(route('galleries.show', $gallery));
        $flag = session('gallery.access.' . $gallery->id);
        $this->assertIsArray($flag);
        $this->assertSame($record->id, $flag['code_id']);
    }
}
