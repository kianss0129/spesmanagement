<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class DashboardController extends Controller
{
    public function redirect()
    {
        $user = auth()->user();

        if (! $user) {
            return Redirect::route('login');
        }

        return match (true) {
            $user->hasRole('Super Admin') || $user->hasRole('Admin') => Redirect::route('admin.dashboard'),
            $user->hasRole('Employer') => Redirect::route('employer.dashboard'),
            $user->hasRole('Beneficiary') => Redirect::route('beneficiary.dashboard'),
            $user->hasRole('PESO') => Redirect::route('peso.dashboard'),

            // ⛔ NEVER abort() in an Inertia flow
            default => Redirect::route('login'),
        };
    }
}
