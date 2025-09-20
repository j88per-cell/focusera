<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Gallery::class => \App\Policies\GalleryPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Global admin override: role_id === 3 passes all abilities
        Gate::before(function (User $user, string $ability = null) {
            return $user->role_id === 3 ? true : null;
        });

        Gate::define('isAdmin', function (User $user) { return $user->role_id === 3; });

        Gate::define('isClient', function (User $user) {
            return $user->role_id === 2;
        });

        Gate::define('isGuest', function (User $user) {
            return $user->role_id === 1;
        });
    }
}
