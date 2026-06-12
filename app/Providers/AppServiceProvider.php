<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admin bypass
        Gate::before(function (User $user, string $ability) {
            if ($user->role === 'admin') {
                return true;
            }

            return null;
        });

        // Projects
        Gate::define('view-projects', function (User $user) {
            return in_array($user->role, ['admin', 'pm', 'engineer', 'viewer']);
        });

        Gate::define('manage-projects', function (User $user) {
            return in_array($user->role, ['admin', 'pm']);
        });

        Gate::define('delete-project', function (User $user) {
            return $user->role === 'admin';
        });

        // Artifacts
        Gate::define('manage-artifacts', function (User $user) {
            return in_array($user->role, ['admin', 'pm']);
        });

        // Modules
        Gate::define('edit-modules', function (User $user) {
            return in_array($user->role, ['admin', 'pm', 'engineer']);
        });

        Gate::define('validate-modules', function (User $user) {
            return in_array($user->role, ['admin', 'pm', 'engineer']);
        });
    }
}
