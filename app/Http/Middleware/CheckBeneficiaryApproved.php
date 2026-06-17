<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBeneficiaryApproved
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        $beneficiary = $user?->beneficiary;

        if (
            ! $user?->hasRole('Beneficiary') ||
            ! $beneficiary ||
            ! ($beneficiary->approved || $beneficiary->approval_status === 'approved')
        ) {
            // redirect if not approved
            return redirect()->route('login')->with('error', 'Your account is not approved yet.');
        }

        return $next($request);
    }
}
