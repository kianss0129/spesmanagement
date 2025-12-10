<?php

namespace App\Http\Controllers\PESO;
use App\Models\JobListing;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use Illuminate\Http\Request;

class PESOController extends Controller
{
    // ✅ Approve / Reject Application
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required'
        ]);

        $app = Application::findOrFail($id);
        $app->update(['status' => $request->status]);

        return response()->json(['message' => 'Status updated']);
    }

    // ✅ Schedule Interview
    public function scheduleInterview(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required',
            'scheduled_at' => 'required|date',
            'meet_link' => 'nullable'
        ]);

        Interview::create($data);

        return response()->json(['message' => 'Interview scheduled']);
    }

    public function assignBeneficiary(Request $request)
{
    $data = $request->validate([
        'job_listing_id' => 'required|exists:job_listings,id',
        'beneficiary_id' => 'required|exists:beneficiaries,id'
    ]);

    // ✅ Update Job Listing
    JobListing::whereId($data['job_listing_id'])
        ->update(['assigned_beneficiary_id' => $data['beneficiary_id']]);

    // ✅ Update Application Status
    Application::where('job_listing_id', $data['job_listing_id'])
        ->where('beneficiary_id', $data['beneficiary_id'])
        ->update(['status' => 'assigned']);

    return response()->json(['message' => 'Beneficiary successfully assigned']);
}
}
