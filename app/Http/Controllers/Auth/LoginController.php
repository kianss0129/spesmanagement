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
            'role' => ['nullable','string','in:peso'],
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

        $user = $request->user();
        $role = $request->input('role');

        if ($role === 'peso') {
            if (! $user->hasRole('PESO') && ! $user->hasRole('PESO Admin') && ! $user->hasRole('Admin') && ! $user->hasRole('Super Admin')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'Only PESO, PESO Admin, or Admin accounts may log in here.',
                ]);
            }
        } else {
            if (! $user->hasRole('Employer') && ! $user->hasRole('Beneficiary')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'Only employer and beneficiary accounts may log in here.',
                ]);
            }
        }

        // Regenerate session
        $request->session()->regenerate();

        // Redirect based on role (fallback handled by authenticated())
        return $this->authenticated($request, $user);
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

    /**
     * Redirect user after login if temporary password
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->is_temporary_password) {
            return redirect()->route('password.change')
                             ->with('warning', 'Please change your temporary password.');
        }

        // Fallback redirect based on role
        if ($user->hasRole('PESO') || $user->hasRole('PESO Admin') || $user->hasRole('Admin') || $user->hasRole('Super Admin')) {
            return redirect()->route('dashboard');
        } elseif ($user->hasRole('Employer')) {
            return redirect()->route('employer.dashboard');
        } elseif ($user->hasRole('Beneficiary')) {
            return redirect()->route('onboarding');
        }

        return redirect('/dashboard'); // fallback
    }
}
