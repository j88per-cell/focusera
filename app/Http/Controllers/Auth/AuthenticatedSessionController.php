<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoginOtpMail;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = User::where('email', $request->email)->firstOrFail();

        $otp = rand(100000, 999999);

        $user->forceFill([
            'otp_code' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(5),
        ])->save();

        // Send via email
        Mail::to($user->email)->send(new \App\Mail\LoginOtpMail($otp));

        return back()->with('status', 'OTP sent!');

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function verify(Request $request) {
        $user = User::where('email', $request->email)->firstOrFail();

        if (! $user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
            return back()->withErrors(['code' => 'OTP expired']);
        }

        if (! Hash::check($request->code, $user->otp_code)) {
            return back()->withErrors(['code' => 'Invalid code']);
        }

        // clear otp
        $user->forceFill(['otp_code' => null, 'otp_expires_at' => null])->save();

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }
}
