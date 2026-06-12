<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Super Admin bypass
        Gate::before(function (User $user, string $ability) {
            if ($user->role === 'admin') {
                return true;
            }

            return null;
        });

        // Projects - Todos pueden ver
        Gate::define('view-projects', function (User $user) {
            return in_array($user->role, ['admin', 'pm', 'engineer', 'viewer']);
        });

        Gate::define('manage-projects', function (User $user) {
            return in_array($user->role, ['admin', 'pm']);
        });

        Gate::define('delete-project', function (User $user) {
            return $user->role === 'admin';
        });

        // -- Artifacts - Todos pueden ver, solo admin/pm pueden editar
        Gate::define('view-artifacts', function (User $user) {
            return in_array($user->role, ['admin', 'pm', 'engineer', 'viewer']);
        });

        Gate::define('manage-artifacts', function (User $user) {
            return in_array($user->role, ['admin', 'pm']);
        });

        // -- CORREGIDO: Modules - Todos pueden VER, solo admin/pm/engineer pueden EDITAR
        Gate::define('view-modules', function (User $user) {
            return in_array($user->role, ['admin', 'pm', 'engineer', 'viewer']);
        });

        Gate::define('edit-modules', function (User $user) {
            return in_array($user->role, ['admin', 'pm', 'engineer']);
        });

        Gate::define('validate-modules', function (User $user) {
            return in_array($user->role, ['admin', 'pm', 'engineer']);
        });

        // -- Agregar Gate para ver usuarios
        Gate::define('view-users', function (User $user) {
            return in_array($user->role, ['admin', 'pm']);
        });
    }
}
