<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(): Response
    {
        $roles = Role::orderBy('id')->get(['id', 'label']);
        $users = User::orderByDesc('created_at')->get(['id', 'name', 'email', 'role_id', 'created_at']);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
        ])->rootView('admin');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email'],
            'name' => ['nullable', 'string', 'max:255'],
            'role_id' => ['required', Rule::exists('roles', 'id')],
        ]);

        $email = strtolower($data['email']);
        $roleId = (int) $data['role_id'];
        $name = $data['name'] ?? null;

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->forceFill([
                'role_id' => $roleId,
                'name' => $name ? $name : $user->name,
            ])->save();
        } else {
            $user = User::create([
                'email' => $email,
                'name' => $name ?: ucfirst(strtok($email, '@')),
                'role_id' => $roleId,
            ]);
        }

        return redirect()->back()->with('status', 'Invitation saved for '.$user->email);
    }
}
