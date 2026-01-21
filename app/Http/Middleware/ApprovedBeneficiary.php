<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApprovedBeneficiary
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user is a Beneficiary
        if ($user->hasRole('Beneficiary')) {
            $beneficiary = $user->beneficiary;

            if (!$beneficiary || !$beneficiary->is_approved) {
                return redirect()->route('beneficiary.uploadDocuments')
                    ->with('error', 'Please upload required documents. Your account is not approved yet.');
            }
        }

        return $next($request);
    }
}
