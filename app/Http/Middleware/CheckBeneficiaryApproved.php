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

        if ($user->role !== 'Beneficiary' || !$user->is_approved) {
            // redirect if not approved
            return redirect()->route('login')->with('error', 'Your account is not approved yet.');
        }

        return $next($request);
    }
}
