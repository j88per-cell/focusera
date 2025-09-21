<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            // Expose all site feature flags as a single bag
            'features' => (function () {
                $site = (array) (config('settings.features') ?? []);
                // Back-compat: include registration if not present in settings
                if (!array_key_exists('registration', $site)) {
                    $site['registration'] = (bool) config('features.registration', false);
                }
                return $site;
            })(),
            'sales' => [
                'enabled' => (bool) (config('settings.sales.enabled') ?? false),
                'default_markup' => (float) (config('settings.sales.markup_percent') ?? 25),
            ],
        ];
    }
}
