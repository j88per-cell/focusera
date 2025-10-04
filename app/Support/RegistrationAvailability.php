<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RegistrationAvailability
{
    public function shouldExposeRegistrationRoute(): bool
    {
        if ($this->featureFlagEnabled()) {
            return true;
        }

        if (! $this->usersTableExists()) {
            return true; // fresh install, allow bootstrap
        }

        return $this->noUsersExist();
    }

    protected function featureFlagEnabled(): bool
    {
        return (bool) Config::get('features.registration', env('ALLOW_REGISTRATION', false));
    }

    protected function usersTableExists(): bool
    {
        try {
            DB::connection()->getPdo();
            return Schema::hasTable('users');
        } catch (Throwable $e) {
            return false;
        }
    }

    protected function noUsersExist(): bool
    {
        try {
            return ! User::query()->exists();
        } catch (Throwable $e) {
            return true;
        }
    }
}
