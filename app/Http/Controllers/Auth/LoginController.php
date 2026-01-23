<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validate inputs including reCAPTCHA
        $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
            'recaptcha' => ['required'], // mandatory now
        ]);

        // Verify Google reCAPTCHA
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret'), // Add your secret in .env
            'response' => $request->recaptcha,
            'remoteip' => $request->ip(),
        ]);

        if (! data_get($response->json(), 'success')) {
            throw ValidationException::withMessages([
                'recaptcha' => 'reCAPTCHA verification failed. Please try again.',
            ]);
        }

        // Attempt login
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        // Regenerate session
        $request->session()->regenerate();

        // Redirect based on role
        $user = $request->user();

        if ($user->hasRole('PESO') || $user->hasRole('PESO Admin')) {
            return redirect()->route('peso.dashboard');
        } elseif ($user->hasRole('Admin') || $user->hasRole('Super Admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('Employer')) {
            return redirect()->route('employer.dashboard');
        } elseif ($user->hasRole('Beneficiary')) {
            return redirect()->route('onboarding'); // or beneficiary dashboard
        }

        return redirect('/dashboard'); // fallback
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
    