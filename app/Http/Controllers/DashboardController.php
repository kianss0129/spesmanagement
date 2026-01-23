<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Redirect users after login based on their role.
     */
    public function redirect()
    {
        $user = auth()->user();

        // If no user is logged in, send to login page
        if (!$user) {
            return Redirect::route('login');
        }

        // Beneficiary onboarding check
        if ($user->hasRole('Beneficiary')) {
            // Make sure you have a column 'onboarding_completed' in users table
            if (! $user->onboarding_completed) {
                return Redirect::route('onboarding');
            }

            // Optional: create a separate beneficiary dashboard later
            // return Redirect::route('beneficiary.dashboard');
            return Redirect::route('onboarding'); // fallback for now
        }

        // Role-based redirect for Admin, Employer, PESO, etc.
        return match (true) {
            $user->hasRole('Super Admin') || $user->hasRole('Admin') => Redirect::route('admin.dashboard'),
            $user->hasRole('Employer') => Redirect::route('employer.dashboard'),
            $user->hasRole('PESO') || $user->hasRole('PESO Admin') => Redirect::route('peso.dashboard'),

            // fallback
            default => Redirect::route('login'),
        };
    }
}
