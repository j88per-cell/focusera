<?php

namespace App\Print;

class PrintConfig
{
    public static function providerKey(): string
    {
        return (string) (config('settings.sales.provider') ?? 'null');
    }

    public static function sandbox(): bool
    {
        $fromSettings = config('settings.sales.sandbox');
        if (!is_null($fromSettings)) {
            return (bool) $fromSettings;
        }
        return (bool) config('print.sandbox', true);
    }

    public static function options(string $provider): array
    {
        $base = (array) (config("print.options.{$provider}") ?? []);

        // Settings-backed options live under group=sales, sub_group="providers.{provider}"
        $sales = (array) (config('settings.sales') ?? []);
        $flat = (array) ($sales["providers.{$provider}"] ?? []);

        // Normalize flat keys like 'endpoint.sandbox' into nested ['endpoint' => ['sandbox' => ...]]
        $normalized = $base;
        foreach ($flat as $key => $val) {
            if ($key === 'api_key' || $key === 'api_secret' || $key === 'token') {
                $normalized[$key] = $val;
                continue;
            }
            if (str_starts_with((string) $key, 'endpoint.')) {
                $parts = explode('.', (string) $key, 2);
                $which = $parts[1] ?? null; // sandbox|live
                if ($which) {
                    $normalized['endpoint'] = $normalized['endpoint'] ?? [];
                    $normalized['endpoint'][$which] = $val;
                }
                continue;
            }
            // Otherwise copy raw
            $normalized[$key] = $val;
        }

        return $normalized;
    }

    public static function endpoint(string $provider): ?string
    {
        $opts = self::options($provider);
        $endpoints = (array) ($opts['endpoint'] ?? []);
        $key = self::sandbox() ? 'sandbox' : 'live';
        return $endpoints[$key] ?? null;
    }
}
