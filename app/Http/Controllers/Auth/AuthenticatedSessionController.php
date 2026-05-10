<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request)
    {
        // ✅ VALIDATION
        $request->validate([
            'email'     => 'required|email',
            'password'  => 'required',
            'recaptcha' => 'required',
        ]);

        // ✅ VERIFY reCAPTCHA
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

        // ✅ AUTHENTICATION
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

            // ✅ EMAIL MUST BE VERIFIED FIRST
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            // ----------------------------------------------------
            // ADMIN
            // ----------------------------------------------------
            if ($user->hasRole('Admin') || $user->hasRole('Super Admin')) {
                return redirect()->route('admin.dashboard');
            }

            // ----------------------------------------------------
            // PESO
            // ----------------------------------------------------
            if ($user->hasRole('PESO')) {
                return redirect()->route('peso.dashboard');
            }

            // ----------------------------------------------------
            // EMPLOYER
            // ----------------------------------------------------
            if ($user->hasRole('Employer')) {

                $employer = $user->employer;

                if (!$employer) {
                    return redirect()->route('onboarding');
                }

                if ($employer->approval_status !== 'approved') {
                    return redirect()->route('onboarding.pending');
                }

                return redirect()->route('employer.dashboard');
            }

            // ----------------------------------------------------
            // BENEFICIARY
            // ----------------------------------------------------
            if ($user->hasRole('Beneficiary')) {

                $beneficiary = $user->beneficiary;

                if (!$beneficiary) {
                    return redirect()->route('onboarding');
                }

                if ($beneficiary->approval_status !== 'approved') {
                    return redirect()->route('onboarding.pending');
                }

                return redirect()->route('beneficiary.dashboard');
            }

            // ----------------------------------------------------
            // DEFAULT (no role yet)
            // ----------------------------------------------------
            return redirect()->route('onboarding');

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
