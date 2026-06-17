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
            return redirect()->route('employer.dashboard')
                ->with('message', 'Please complete your employer profile.');
        }

        // If rejected
        if ($employer->approval_status === 'rejected') {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account was rejected by PESO.']);
        }

        // If pending - allow dashboard access with limited access
        if ($employer->approval_status === 'pending') {
            return redirect()->route('employer.dashboard')
                ->with('message', 'Your account is pending approval. Complete your profile to speed up the process.');
        }

        // Allow only approved
        if ($employer->approval_status !== 'approved') {
            return redirect()->route('employer.dashboard');
        }

        return $next($request);
    }
}