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
            ->get(['id','group','sub_group','key','value','description']);

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
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
