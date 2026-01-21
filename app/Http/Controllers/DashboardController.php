<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function redirect()
    {
        $user = auth()->user();

        if (!$user) {
            return Redirect::route('login');
        }

        // Beneficiary onboarding check
        if ($user->hasRole('Beneficiary')) {
            // Assume you have a column 'onboarding_completed' in users table
            if (! $user->onboarding_completed) {
                return Redirect::route('onboarding');
            }

            // Optional: If you later create a beneficiary dashboard
            // return Redirect::route('beneficiary.dashboard');
            return Redirect::route('onboarding'); // fallback for now
        }

        return match (true) {
            $user->hasRole('Super Admin') || $user->hasRole('Admin') => Redirect::route('admin.dashboard'),
            $user->hasRole('Employer') => Redirect::route('employer.dashboard'),
            $user->hasRole('PESO') => Redirect::route('peso.dashboard'),

            default => Redirect::route('login'),
        };
    }
}
