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

            // Pipe common groups to top-level config to avoid cache friction
            if (!empty($config['features']) && is_array($config['features'])) {
                $mergedFeatures = array_merge((array) config('features', []), (array) $config['features']);
                config(['features' => $mergedFeatures]);
            }
            if (!empty($config['sales']) && is_array($config['sales'])) {
                $mergedSales = array_merge((array) config('sales', []), (array) $config['sales']);
                config(['sales' => $mergedSales]);
            }
            if (!empty($config['site']) && is_array($config['site'])) {
                $mergedSite = array_merge((array) config('site', []), (array) $config['site']);
                config(['site' => $mergedSite]);
            }
        }
    }
}
