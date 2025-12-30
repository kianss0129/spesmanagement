<?php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login'); // ✅ path to your Vue page: resources/js/Pages/Auth/Login.vue
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
                $request->session()->regenerate();

                $user = Auth::user();

                // Role-aware redirect to avoid extra roundtrips and ensure the correct dashboard
                // Role-aware redirects. Use a case-insensitive check to handle role name casing differences.
                // Use direct DB checks so role name casing and cached permission issues don't prevent correct redirects.
                if ($user->hasRole('PESO') || $user->roles()->whereRaw('LOWER(name) = ?', ['peso'])->exists()) {
                    return redirect()->route('peso.dashboard');
                }

                if ($user->hasRole('Employer') || $user->roles()->whereRaw('LOWER(name) = ?', ['employer'])->exists()) {
                    return redirect()->route('employer.dashboard');
                }

                if ($user->hasRole('Beneficiary') || $user->roles()->whereRaw('LOWER(name) = ?', ['beneficiary'])->exists()) {
                    return redirect()->route('beneficiary.dashboard');
                }

                if ($user->hasRole('Admin') || $user->hasRole('Super Admin') || $user->roles()->whereRaw('LOWER(name) IN (?,?)', ['admin','super admin'])->exists()) {
                    return redirect()->route('admin.dashboard');
                }

                // Fallback to the generic dashboard route
                return redirect()->intended(route('dashboard'));
            }

            return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->withInput();
        } catch (\Exception $e) {
            Log::error('Login error: '.$e->getMessage());
            return back()->withErrors(['email' => 'An error occurred during login.']);
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
