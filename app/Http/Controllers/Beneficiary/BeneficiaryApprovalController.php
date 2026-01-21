<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BeneficiaryApprovalController extends Controller
{
    /**
     * List all pending beneficiaries
     */
    public function index()
    {
        $beneficiaries = Beneficiary::where('approval_status', 'pending')->get();

        return response()->json($beneficiaries);
    }

    /**
     * View beneficiary details + documents
     */
    public function show($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);

        return response()->json($beneficiary);
    }

    /**
     * Approve beneficiary
     */
    public function approve($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);

        // Generate temporary password
        $password = Str::random(10);

        // Update linked user
        $user = $beneficiary->user;

        if ($user) {
            $user->password = Hash::make($password);
            $user->save();
        }

        $beneficiary->update([
            'approved' => true,
            'approval_status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
            'resubmit_at' => null,
        ]);

        // Send email
        Mail::raw(
            "Your SPES account has been approved.\n\nLogin Email: {$beneficiary->email}\nTemporary Password: {$password}\n\nPlease login and change your password immediately.\n\nSPES Office",
            function ($message) use ($beneficiary) {
                $message->to($beneficiary->email)
                        ->subject('SPES Account Approved');
            }
        );

        return response()->json(['message' => 'Beneficiary approved and email sent']);
    }

    /**
     * Reject beneficiary
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:5'
        ]);

        $beneficiary = Beneficiary::findOrFail($id);

        $beneficiary->update([
            'approved' => false,
            'approval_status' => 'rejected',
            'rejection_reason' => $request->reason,
            'resubmit_at' => Carbon::now()->addDays(5),
        ]);

        // Send rejection email
        Mail::raw(
            "Your SPES document submission was rejected.\n\nReason: {$request->reason}\n\nYou may resubmit your documents after 5 days.\n\nSPES Office",
            function ($message) use ($beneficiary) {
                $message->to($beneficiary->email)
                        ->subject('SPES Document Submission Rejected');
            }
        );

        return response()->json(['message' => 'Beneficiary rejected and notified']);
    }
}
