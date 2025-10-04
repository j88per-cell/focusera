<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Throwable;

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
        if (! $this->databaseIsReady()) {
            return;
        }

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
                $mergedSite = array_replace_recursive((array) config('site', []), (array) $config['site']);

                $publicDisk = $mergedSite['storage']['public_disk'] ?? 'photos_public';
                $privateDisk = $mergedSite['storage']['private_disk'] ?? 'photos_private';

                $publicBase = null;
                try {
                    $baseUrl = Storage::disk($publicDisk)->url('');
                    if (is_string($baseUrl) && $baseUrl !== '') {
                    $publicBase = rtrim($baseUrl, '/');
                }
            } catch (\Throwable $e) {
                $publicBase = null;
            }

                $mergedSite['storage']['public_disk'] = $publicDisk;
                $mergedSite['storage']['private_disk'] = $privateDisk;
                if ($publicBase) {
                    $mergedSite['storage']['public_base_url'] = $publicBase;
                }

                config(['site' => $mergedSite]);
            }
        }
    }

    protected function databaseIsReady(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}
