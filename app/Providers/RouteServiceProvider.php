<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

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
        $this->configureRateLimiting();

        parent::boot();

        // ✅ Optional: Route logic based on role
        Route::middleware('web')->group(function () {
            Route::get('/dashboard', function () {
                $role = auth()->user()->getRoleNames()->first();

                return match ($role) {
                    'Admin' => redirect('/admin-dashboard'),
                    'PESO' => redirect('/peso-user/dashboard'),
                    'PESO User' => redirect('/peso-user/dashboard'),
                    'Employer' => redirect('/employer/dashboard'),
                    'Beneficiary' => redirect('/beneficiary/dashboard'),
                    'Super Admin' => redirect('/admin-dashboard'),
                    default => abort(403),
                };
            });
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('heavy-routes', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('heavy-actions', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
