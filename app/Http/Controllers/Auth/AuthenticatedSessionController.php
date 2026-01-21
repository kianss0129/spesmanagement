<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; // ✅ ADDED
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request)
    {
        // ----------------------------------------------------
        // ✅ VALIDATION (ADDED recaptcha)
        // ----------------------------------------------------
        $request->validate([
            'email'     => 'required|email',
            'password'  => 'required',
            'recaptcha' => 'required',
        ]);

        // ----------------------------------------------------
        // ✅ VERIFY reCAPTCHA WITH GOOGLE
        // ----------------------------------------------------
        try {
            $recaptchaResponse = Http::asForm()->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'secret'   => config('services.recaptcha.secret'),
                    'response' => $request->recaptcha,
                    'ip'       => $request->ip(),
                ]
            );

            if (!data_get($recaptchaResponse->json(), 'success')) {
                return back()->withErrors([
                    'recaptcha' => 'reCAPTCHA verification failed. Please try again.',
                ])->withInput();
            }
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA error', [
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'recaptcha' => 'Unable to verify reCAPTCHA. Please try again.',
            ])->withInput();
        }

        // ----------------------------------------------------
        // AUTHENTICATION
        // ----------------------------------------------------
        try {
            if (!Auth::attempt(
                $request->only('email', 'password'),
                $request->filled('remember')
            )) {
                return back()
                    ->withErrors(['email' => 'The provided credentials do not match our records.'])
                    ->withInput();
            }

            $request->session()->regenerate();
            $user = Auth::user();

            // ----------------------------------------------------
            // ADMIN
            // ----------------------------------------------------
            if (
                $user->hasRole('Admin') ||
                $user->hasRole('Super Admin') ||
                $user->roles()->whereRaw('LOWER(name) IN (?, ?)', ['admin', 'super admin'])->exists()
            ) {
                return redirect()->route('admin.dashboard');
            }

            // ----------------------------------------------------
            // PESO
            // ----------------------------------------------------
            if (
                $user->hasRole('PESO') ||
                $user->roles()->whereRaw('LOWER(name) = ?', ['peso'])->exists()
            ) {
                return redirect()->route('peso.dashboard');
            }

            // ----------------------------------------------------
            // EMPLOYER
            // ----------------------------------------------------
            if (
                $user->hasRole('Employer') ||
                $user->roles()->whereRaw('LOWER(name) = ?', ['employer'])->exists()
            ) {
                return redirect()->route('employer.dashboard');
            }

            // ----------------------------------------------------
            // BENEFICIARY (NEW FLOW)
            // ----------------------------------------------------
            if (
                $user->hasRole('Beneficiary') ||
                $user->roles()->whereRaw('LOWER(name) = ?', ['beneficiary'])->exists()
            ) {
                if ($user->beneficiary_status !== 'approved') {
                    return redirect()->route('onboarding');
                }

                return redirect()->route('beneficiary.dashboard');
            }

            // ----------------------------------------------------
            // DEFAULT USER (no role yet)
            // ----------------------------------------------------
            if ($user->role === 'user' || empty($user->role)) {
                return redirect()->route('onboarding');
            }

            // ----------------------------------------------------
            // FALLBACK
            // ----------------------------------------------------
            return redirect()->intended('/');

        } catch (\Throwable $e) {
            Log::error('Login error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'email' => 'An internal error occurred during login. Please try again.',
            ]);
        }
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Logged out successfully.');
    }
}
