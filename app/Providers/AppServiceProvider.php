<?php

namespace App\Providers;

use Inertia\Inertia;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Anhskohbo\NoCaptcha\Facades\NoCaptcha;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ✅ Fix for role-based access (Spatie)
        $this->app->bind('role', RoleMiddleware::class);

        // ✅ Fix for forgot-password broker error
        $this->app->bind('auth.password', function () {
            return Password::broker();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share a minimal auth payload with Inertia to avoid heavy DB loads
        Inertia::share([
            'auth' => fn () => [
                // Only include minimal user fields to prevent expensive Eloquent loads during boot
                'user' => Auth::check() ? array_merge(Auth::user()->only(['id','name','email','role']), ['roles' => Auth::user()->roles->pluck('name') ?? []]) : null,
                'csrf_token' => csrf_token(),
            ],
        ]);

        // ✅ Custom CAPTCHA validator using NoCaptcha package
        Validator::extend('captcha', function ($attribute, $value, $parameters, $validator) {
            return NoCaptcha::verifyResponse($value);
        });

        Validator::replacer('captcha', function ($message, $attribute) {
            return 'reCAPTCHA verification failed. Please try again.';
        });
    }
}
