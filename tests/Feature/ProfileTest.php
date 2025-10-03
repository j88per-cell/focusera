<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $sessionToken = 'test-token';

        $response = $this
            ->actingAs($user)
            ->withSession(['_token' => $sessionToken])
            ->patch('/profile', [
                '_token' => $sessionToken,
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $sessionToken = 'test-token';

        $response = $this
            ->actingAs($user)
            ->withSession(['_token' => $sessionToken])
            ->patch('/profile', [
                '_token' => $sessionToken,
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account_without_password(): void
    {
        $user = User::factory()->create();

        $sessionToken = 'test-token';

        $response = $this
            ->actingAs($user)
            ->withSession(['_token' => $sessionToken])
            ->delete('/profile', ['_token' => $sessionToken]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }
}
