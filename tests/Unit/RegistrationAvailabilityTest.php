<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\RegistrationAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class RegistrationAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_allows_when_feature_flag_enabled(): void
    {
        Config::set('features.registration', true);

        $availability = new RegistrationAvailability();

        $this->assertTrue($availability->shouldExposeRegistrationRoute());
    }

    public function test_allows_when_database_unavailable(): void
    {
        Config::set('features.registration', false);

        DB::shouldReceive('connection->getPdo')->andThrow(new \PDOException('no driver'));

        $availability = new RegistrationAvailability();

        $this->assertTrue($availability->shouldExposeRegistrationRoute());
    }

    public function test_allows_when_no_users_exist(): void
    {
        Config::set('features.registration', false);

        $availability = new RegistrationAvailability();

        $this->assertTrue($availability->shouldExposeRegistrationRoute());
    }

    public function test_disallows_when_user_exists_and_flag_disabled(): void
    {
        Config::set('features.registration', false);

        User::factory()->create();

        $availability = new RegistrationAvailability();

        $this->assertFalse($availability->shouldExposeRegistrationRoute());
    }
}
