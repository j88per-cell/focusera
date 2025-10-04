<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        return Inertia::render('Settings/Index', ['settings' => $settings]);
    }

    public function updateSetting(UpdateSettingRequest $request, Setting $setting): RedirectResponse
    {
        $input = $request->validated();
        try {
            $setting->update($input);
            Config::set($setting->group.'.'.$setting->sub_group.'.'.$setting->key, $setting->value);
        } catch (\Throwable $th) {
            Log::error('SettingsController::updateSetting(): ' . $th->getMessage());
            return redirect()->back()->withErrors(['message' => $th->getMessage()], 'updateAccount');
        }

        return redirect(route('settings.index'));
    }
}
