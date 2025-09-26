<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        if (User::count() > 1) {
            abort(403, 'Registration is disabled');
        }
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $existingCount = User::count();
        if ($existingCount > 1) {
            abort(403, 'Registration is disabled');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class
        ]);

        $roleId = $existingCount === 0 ? 3 : 1;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $roleId,
        ]);

        event(new Registered($user));

        return redirect()->route('verification.notice');
    }
}
