<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

use App\Models\Setting;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('settings')) {
            $all = Setting::get();

            $config = [];

            foreach ($all as $row) {
                // Build nested array: settings[group][sub_group][key] = value
                if ($row->sub_group) {
                    $config[$row->group][$row->sub_group][$row->key] = $row->value;
                } else {
                    $config[$row->group][$row->key] = $row->value;
                }
            }

            // Merge into Laravel's config bag under "settings"
        config(['settings' => $config]);
        }
    }
}
