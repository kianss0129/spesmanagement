<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureBeneficiaryApproved
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Must be beneficiary
        if (!$user->hasRole('Beneficiary')) {
            return redirect()->route('onboarding')
                ->with('error', 'Access denied.');
        }

        // Must have beneficiary profile
        $beneficiary = $user->beneficiary;

        if (!$beneficiary) {
            return redirect()->route('onboarding.pending')
                ->with('error', 'Please complete your beneficiary profile.');
        }

        /**
         * IMPORTANT FIX
         *
         * Allow access to onboarding.pending
         * even if already approved.
         *
         * Ito kasi yung dahilan bakit
         * auto redirect ka lagi.
         */
        if ($request->routeIs('onboarding.pending')) {
            return $next($request);
        }

        // Must be approved for beneficiary pages
        if ($beneficiary->approval_status !== 'approved') {

            // Rejected with waiting period
            if (
                $beneficiary->approval_status === 'rejected' &&
                $beneficiary->resubmit_at &&
                now()->lt($beneficiary->resubmit_at)
            ) {
                return redirect()->route('onboarding.pending')
                    ->with(
                        'error',
                        'Your documents were rejected. You may resubmit after ' .
                        $beneficiary->resubmit_at->toFormattedDateString()
                    );
            }

            return redirect()->route('onboarding.pending')
                ->with(
                    'error',
                    'Your account is pending approval. Please wait for PESO approval.'
                );
        }

        return $next($request);
    }
}