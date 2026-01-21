<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmployerApproved
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Make sure the user is an employer
        if ($user->role !== 'employer') {
            return redirect()->route('onboarding')->with('error', 'Access denied.');
        }

        // Check if the employer is approved
        // Assuming you have an `approvals` table with `status`
        $latestApproval = $user->approvals()->latest()->first();

        if ($user->role !== 'employer' || $user->approvals()->latest()->first()?->status !== 'approved') {
    return redirect()->route('upload.documents')->with('error', 'Your account is not approved yet.');
}

        return $next($request);
    }
}
