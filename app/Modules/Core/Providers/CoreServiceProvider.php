<?php

namespace App\Modules\Core\Providers;

use App\Modules\Core\Enums\Role;
use App\Modules\Core\Http\Middleware\EnsureAdminArea;
use App\Modules\Core\Http\Middleware\EnsurePasswordChanged;
use App\Modules\Core\Models\User;
use App\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class CoreServiceProvider extends ModuleServiceProvider
{
    protected function viewNamespace(): string
    {
        return 'core';
    }

    public function boot(): void
    {
        Route::aliasMiddleware('admin.area', EnsureAdminArea::class);
        Route::aliasMiddleware('password.changed', EnsurePasswordChanged::class);

        // Super Administrator lolos semua pengecekan permission (company profile, PPDB, dst.).
        Gate::before(function (User $user) {
            return $user->hasRole(Role::SuperAdmin->value) ? true : null;
        });

        parent::boot();
    }
}
