<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_route_is_available_when_no_users_exist(): void
    {
        config(['features.registration' => false]);

        $response = $this->get('/register');

        $response->assertOk();
    }

    public function test_register_route_respects_feature_flag_after_user_exists(): void
    {
        User::factory()->create();
        config(['features.registration' => false]);

        $response = $this->get('/register');

        $response->assertNotFound();
    }

    public function test_register_route_available_when_feature_flag_enabled(): void
    {
        User::factory()->create();
        config(['features.registration' => true]);

        $response = $this->get('/register');

        $response->assertOk();
    }
}
