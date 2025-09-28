<?php

namespace App\Ensurers;

use App\Models\Setting;

class DefaultSettingsEnsurer
{
    public function run(): void
    {
        $this->ensureThemeDefaults();
        $this->ensureGeneralDefaults();
        $this->ensureSecurityDefaults();
        $this->ensureLandingDefaults();
        $this->ensureAnalyticsDefaults();
        $this->ensureFeatureDefaults();
        $this->ensureSalesDefaults();
        $this->repairSecretPlaceholders();
    }

    protected function ensureThemeDefaults(): void
    {
        $this->upsert('site', 'theme', 'active', 'Twilight');

        // Remove legacy records that conflict with the consolidated theme key
        Setting::where('group', 'app')->where('sub_group', 'theme')->where('key', 'active')->delete();
        Setting::where('group', 'site')->where('sub_group', 'theme')->where('key', 'theme')->delete();
    }

    protected function ensureGeneralDefaults(): void
    {
        $this->upsert('site', null, 'photoproxy', '0');
        $this->upsert('site', 'storage', 'public_disk', 'photos_public');
        $this->upsert('site', 'storage', 'private_disk', 'photos_private');
        $this->upsert('site', 'general', 'site_name', 'Focusera');
        $this->upsert('site', 'general', 'landing_page_text', 'Showcase your photography, share securely, and keep full control over client access.');
    }

    protected function ensureSecurityDefaults(): void
    {
        $blockedAgents = [
            'googlebot',
            'bingbot',
            'slurp',
            'duckduckbot',
            'baiduspider',
            'yandexbot',
            'sogou',
            'exabot',
            'facebot',
            'ia_archiver',
            'semrushbot',
            'ahrefsbot',
            'mj12bot',
            'rogerbot',
            'dotbot',
            'bytespider',
        ];

        $this->upsert('site', 'security', 'block_known_bots', '1');
        $this->upsert('site', 'security', 'include_default_bot_list', '1');
        $this->upsert('site', 'security', 'blocked_user_agents', $blockedAgents);
        $this->upsert('site', 'security', 'blocked_user_agent_regex', []);
        $this->upsert('site', 'security', 'allowed_user_agents', []);
        $this->upsert('site', 'security', 'trusted_user_agents', []);
        $this->upsert('site', 'security', 'allow_blank_user_agent', '0');
        $this->upsert('site', 'security', 'require_browser_headers', '1');
        $this->upsert('site', 'security', 'blocked_ips', []);
    }

    protected function ensureLandingDefaults(): void
    {
        $this->upsert('site', 'landing', 'hero_title', "Capturing Life's Beautiful Moments");
        $this->upsert('site', 'landing', 'hero_subtitle', 'Professional photography that tells your story');
        $this->upsert('site', 'landing', 'hero_images', [
            ['src' => '/placeholder.svg?height=500&width=1200', 'alt' => 'Wedding Photography'],
            ['src' => '/placeholder.svg?height=500&width=1200', 'alt' => 'Portrait Photography'],
            ['src' => '/placeholder.svg?height=500&width=1200', 'alt' => 'Landscape Photography'],
        ]);
    }

    protected function ensureAnalyticsDefaults(): void
    {
        $this->upsert('site', 'analytics', 'enabled', '1');
        $this->upsert('site', 'analytics', 'capture_referrer', '1');
        $this->upsert('site', 'analytics', 'capture_geo', '0');
        $this->upsert('site', 'analytics', 'queue', 'analytics');
    }

    protected function ensureFeatureDefaults(): void
    {
        $this->upsert('features', null, 'featured_galleries', '1');
        $this->upsert('features', null, 'news', '1');
        $this->upsert('features', null, 'sales', '0');
    }

    protected function ensureSalesDefaults(): void
    {
        $this->upsert('sales', null, 'provider', 'null');
        $this->upsert('sales', null, 'sandbox', '1');

        foreach (['artelo', 'pictorem', 'lumaprints', 'finerworks'] as $provider) {
            $subGroup = 'providers.' . $provider;
            $opts = (array) (config("print.options.$provider") ?? []);
            $endpoints = (array) ($opts['endpoint'] ?? []);

            $this->upsert('sales', $subGroup, 'endpoint.sandbox', (string) ($endpoints['sandbox'] ?? ''));
            $this->upsert('sales', $subGroup, 'endpoint.live', (string) ($endpoints['live'] ?? ''));
            $this->upsert('sales', $subGroup, 'api_key', null);
            $this->upsert('sales', $subGroup, 'api_secret', null);
        }
    }

    protected function repairSecretPlaceholders(): void
    {
        $secrets = Setting::query()
            ->where('group', 'sales')
            ->where('sub_group', 'like', 'providers.%')
            ->whereIn('key', ['api_key', 'api_secret', 'token'])
            ->get();

        foreach ($secrets as $setting) {
            if (($setting->value ?? '') === '') {
                $setting->value = null;
                $setting->save();
            }
        }
    }

    protected function upsert(string $group, ?string $subgroup, string $key, $value, ?string $description = null): void
    {
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
