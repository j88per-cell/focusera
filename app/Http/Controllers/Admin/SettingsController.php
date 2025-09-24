<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Inertia\Inertia;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::query()
            ->orderBy('group')
            ->orderBy('sub_group')
            ->orderBy('key')
            ->get(['id','group','sub_group','key','value','description'])
            ->map(function ($s) {
                if (method_exists($s, 'isSecret') && $s->isSecret()) {
                    // Mask secret values in payload
                    $s->value = null;
                }
                return $s;
            });

        $providerKeys = array_keys((array) config('print.providers'));
        $providerDefaults = [];
        $opts = (array) config('print.options');
        foreach ($opts as $key => $cfg) {
            $providerDefaults[$key] = [
                'endpoint' => [
                    'sandbox' => $cfg['endpoint']['sandbox'] ?? null,
                    'live' => $cfg['endpoint']['live'] ?? null,
                ],
            ];
        }

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
            'provider_keys' => $providerKeys,
            'provider_defaults' => $providerDefaults,
        ])->rootView('admin');
    }

    public function update(Request $request, Setting $setting)
    {
        $data = $request->validate([
            'value' => ['nullable', 'string'],
        ]);
        $setting->update(['value' => $data['value'] ?? null]);
        // Optional: we could also refresh config('settings') here on next request via provider
        return back(303);
    }
}
