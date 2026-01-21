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

        // Must be beneficiary (Spatie role)
        if (!$user->hasRole('Beneficiary')) {
            return redirect()->route('onboarding')
                ->with('error', 'Access denied.');
        }

        // Must have beneficiary profile
        $beneficiary = $user->beneficiary;

        if (!$beneficiary) {
            return redirect()->route('beneficiary.page.uploadDocuments')
                ->with('error', 'Please complete your beneficiary profile.');
        }

        // Must be approved
        if (!$beneficiary->approved || $beneficiary->approval_status !== 'approved') {

            // If rejected and waiting period
            if ($beneficiary->approval_status === 'rejected' && $beneficiary->resubmit_at && now()->lt($beneficiary->resubmit_at)) {
                return redirect()->route('beneficiary.page.uploadDocuments')
                    ->with('error', 'Your documents were rejected. You may resubmit after ' . $beneficiary->resubmit_at->toFormattedDateString());
            }

            return redirect()->route('beneficiary.page.uploadDocuments')
                ->with('error', 'Your account is pending approval. Please upload required documents.');
        }

        return $next($request);
    }
}
