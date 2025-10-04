<?php

namespace Tests\Feature;

use App\Mail\GalleryAccessCodeMail;
use App\Models\Gallery;
use App\Models\GalleryAccessCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminGalleryCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_admin_can_generate_access_code_and_email_is_sent(): void
    {
        config(['site.analytics.enabled' => false]);
        Mail::fake();

        $admin = User::factory()->create(['role_id' => 3]);
        $gallery = Gallery::factory()->create();

        $response = $this->actingAs($admin)->postJson(
            route('admin.galleries.codes.store', $gallery),
            [
                'email' => 'client@example.com',
                'duration' => '7d',
                'label' => 'Client Preview',
            ]
        );

        $response->assertCreated();

        $payload = $response->json();
        $this->assertArrayHasKey('id', $payload);
        $this->assertArrayHasKey('code', $payload);
        $this->assertArrayHasKey('link', $payload);
        $this->assertArrayHasKey('expires_at', $payload);

        $record = GalleryAccessCode::find($payload['id']);
        $this->assertNotNull($record);
        $this->assertSame($gallery->id, $record->gallery_id);
        $this->assertSame('Client Preview', $record->label);
        $this->assertTrue(Hash::check($payload['code'], $record->code_hash));

        Mail::assertSent(GalleryAccessCodeMail::class, function (GalleryAccessCodeMail $mail) use ($gallery, $payload) {
            return $mail->hasTo('client@example.com') && str_contains($payload['link'], (string) $gallery->id);
        });
    }
}
