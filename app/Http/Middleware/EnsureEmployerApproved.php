<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmployerApproved
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check Spatie role
        if (!$user->hasRole('Employer')) {
            return redirect()->route('onboarding')
                ->with('error', 'Access denied.');
        }

        $employer = $user->employer;

        if (!$employer) {
            return redirect()->route('onboarding')
                ->with('error', 'Please complete your employer profile.');
        }

        // If rejected
        if ($employer->approval_status === 'rejected') {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account was rejected by PESO.']);
        }

        // If pending
        if ($employer->approval_status === 'pending') {
            return redirect()->route('onboarding.pending')
                ->with('error', 'Your account is still pending approval.');
        }

        // Allow only approved
        if ($employer->approval_status !== 'approved') {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}