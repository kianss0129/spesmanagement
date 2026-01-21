<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // ✅ Validate inputs
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'recaptcha' => ['required'],
        ]);

        // ✅ Verify reCAPTCHA
        $response = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret' => config('services.recaptcha.secret'),
                'response' => $request->recaptcha,
                'remoteip' => $request->ip(),
            ]
        );

        if (!data_get($response->json(), 'success')) {
            throw ValidationException::withMessages([
                'recaptcha' => 'reCAPTCHA verification failed.',
            ]);
        }

        // ✅ Attempt login
        if (!Auth::attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        // ✅ Regenerate session
        $request->session()->regenerate();

        // ✅ Redirect after login
        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
