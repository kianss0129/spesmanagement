<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

// ✅ Add Spatie middleware classes
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/welcome-after-register';
    

    /**
     * Register any application services.
     */
    public function register()
    {
        //
    }

    /**
     * Define your route model bindings, pattern filters, and other route services.
     */
    public function boot(): void
    {
        parent::boot();

        // ✅ Manually bind the middleware aliases for Spatie
        Route::aliasMiddleware('role', RoleMiddleware::class);
        Route::aliasMiddleware('permission', PermissionMiddleware::class);
        Route::aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);

        // ✅ Optional: Route logic based on role
        Route::middleware('web')->group(function () {
            Route::get('/dashboard', function () {
                $role = auth()->user()->getRoleNames()->first();

                return match ($role) {
                    'Admin' => redirect('/admin-dashboard'),
                    'PESO' => redirect('/peso/dashboard'),
                    'Employer' => redirect('/employer/dashboard'),
                    'Beneficiary' => redirect('/beneficiary/dashboard'),
                    'Super Admin' => redirect('/admin-dashboard'),
                    default => abort(403),
                };
            });
        });
    }
}
