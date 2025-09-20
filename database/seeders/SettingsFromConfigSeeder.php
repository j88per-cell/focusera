<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsFromConfigSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFromArray('features', config('features', []));
        $this->seedPhotos(config('photos', []));
        // Sales defaults (provider-agnostic)
        $this->upsert('sales', null, 'markup_percent', 25);
    }

    protected function seedFromArray(string $group, array $data, ?string $sub = null): void
    {
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                // Store nested arrays one level down as their own subgroup entries
                $this->seedFromArray($group, $val, $sub ? ($sub . '.' . $key) : (string) $key);
            } else {
                $this->upsert($group, $sub, (string) $key, $val);
            }
        }
    }

    protected function seedPhotos(array $photos): void
    {
        foreach ($photos as $key => $val) {
            if (is_array($val)) {
                // If it's a list (numeric keys), store as a single JSON value under the key
                $isList = array_values($val) === $val;
                if ($isList) {
                    $this->upsert('photos', null, (string) $key, $val);
                } else {
                    // Associative array: treat as subgroup entries
                    foreach ($val as $k => $v) {
                        $this->upsert('photos', (string) $key, (string) $k, $v);
                    }
                }
            } else {
                $this->upsert('photos', null, (string) $key, $val);
            }
        }
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
