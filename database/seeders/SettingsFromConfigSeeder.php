<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsFromConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Site/Theme defaults only (strip mock/config-derived seeds)
        // Ensure the active app theme is set for blade root view resolution
        $this->upsert('app', 'theme', 'active', 'Default');
        // Feature toggles (default enabled) live under group: features
        $this->upsert('features', null, 'featured_galleries', '1');
        $this->upsert('features', null, 'news', '1');
    }

    protected function upsert(string $group, ?string $subgroup, string $key, $value, ?string $description = null): void
    {
        // Normalize value to string; JSON-encode arrays/objects; cast bool/int
        if (is_bool($value)) {
            $str = $value ? '1' : '0';
        } elseif (is_array($value) || is_object($value)) {
            $str = json_encode($value, JSON_UNESCAPED_SLASHES);
        } else {
            $str = (string) $value;
        }

        Setting::updateOrCreate(
            [
                'group' => $group,
                'sub_group' => $subgroup,
                'key' => $key,
            ],
            [
                'value' => $str,
                'description' => $description,
            ]
        );
    }
}
