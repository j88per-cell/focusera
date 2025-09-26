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
            'ui' => [
                'open_login' => (bool) ($request->session()->get('open_login') ?? false),
            ],
            // Expose all feature flags (top-level bag merged from settings)
            'features' => (function () {
                $all = (array) (config('features') ?? []);
                if (!array_key_exists('registration', $all)) {
                    $all['registration'] = (bool) config('features.registration', false);
                }
                return $all;
            })(),
            // Expose site settings (theme, etc.)
            'site' => (array) (config('site') ?? config('settings.site') ?? []),
            // Do not expose non-site settings (e.g., sales/provider secrets) to the UI
            'sales' => [
                'enabled' => (bool) (config('settings.sales.enabled') ?? false),
                'default_markup' => (float) (config('settings.sales.markup_percent') ?? 25),
            ],
        ];
    }
}
