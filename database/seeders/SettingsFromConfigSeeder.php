<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsFromConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Theme under site group (used by blade root view)
        $this->upsert('site', 'theme', 'active', 'Twilight');
        // Cleanup legacy keys
        \App\Models\Setting::where('group', 'app')->where('sub_group', 'theme')->where('key', 'active')->delete();
        \App\Models\Setting::where('group', 'site')->where('sub_group', 'theme')->where('key', 'theme')->delete();
        $this->upsert('site', null, 'photoproxy', '0');
        $this->upsert('site', 'storage', 'public_disk', 'photos_public');
        $this->upsert('site', 'storage', 'private_disk', 'photos_private');
        $this->upsert('site', 'general', 'site_name', 'Focusera');
        $this->upsert('site', 'general', 'landing_page_text', 'Showcase your photography, share securely, and keep full control over client access.');
        $this->upsert('site', 'analytics', 'enabled', '1');
        $this->upsert('site', 'analytics', 'capture_referrer', '1');
        $this->upsert('site', 'analytics', 'capture_geo', '0');
        $this->upsert('site', 'analytics', 'queue', 'analytics');
        // Feature toggles (default enabled) live under group: features
        $this->upsert('features', null, 'featured_galleries', '1');
        $this->upsert('features', null, 'news', '1');
        // Sales feature (ordering/cart). Default OFF until configured
        $this->upsert('features', null, 'sales', '0');
        // Default POD provider key and sandbox flag
        $this->upsert('sales', null, 'provider', 'null');
        $this->upsert('sales', null, 'sandbox', '1');

        // Provider placeholders (so they’re editable in Admin UI)
        foreach (['artelo','pictorem','lumaprints','finerworks'] as $prov) {
            $sub = 'providers.' . $prov;
            $opts = (array) (config("print.options.$prov") ?? []);
            $endpoints = (array) ($opts['endpoint'] ?? []);
            $this->upsert('sales', $sub, 'endpoint.sandbox', (string) ($endpoints['sandbox'] ?? ''));
            $this->upsert('sales', $sub, 'endpoint.live', (string) ($endpoints['live'] ?? ''));
            $this->upsert('sales', $sub, 'api_key', null);
            $this->upsert('sales', $sub, 'api_secret', null);
        }

        // Repair: if any secret fields were seeded as empty-string and got encrypted, reset them to null
        $secrets = \App\Models\Setting::query()
            ->where('group', 'sales')
            ->where('sub_group', 'like', 'providers.%')
            ->whereIn('key', ['api_key','api_secret','token'])
            ->get();
        foreach ($secrets as $s) {
            if (($s->value ?? '') === '') { // accessor decrypts if needed
                $s->value = null; // mutator will store as null
                $s->save();
            }
        }
    }

    protected function upsert(string $group, ?string $subgroup, string $key, $value, ?string $description = null): void
    {
        // Allow null storage; otherwise normalize scalars
        $toStore = null;
        if (!is_null($value)) {
            if (is_bool($value)) {
                $toStore = $value ? '1' : '0';
            } elseif (is_array($value) || is_object($value)) {
                $toStore = json_encode($value, JSON_UNESCAPED_SLASHES);
            } else {
                $toStore = (string) $value;
            }
        }

        Setting::updateOrCreate(
            [
                'group' => $group,
                'sub_group' => $subgroup,
                'key' => $key,
            ],
            [
                'value' => $toStore,
                'description' => $description,
            ]
        );
    }
}
