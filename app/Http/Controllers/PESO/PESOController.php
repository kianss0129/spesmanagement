<?php

namespace App\Http\Controllers\PESO;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Models\JobListing;
use App\Models\Beneficiary;
use App\Models\Employer;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PESOController extends Controller
{
    /**
     * PESO Dashboard
     */
    public function dashboard()
    {
        return Inertia::render('PESO/Dashboard');
    }

    /**
     * Approve / Reject Application
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required']);

        $app = Application::findOrFail($id);
        $app->update(['status' => $request->status]);

        return response()->json(['message' => 'Status updated']);
    }

    /**
     * Schedule Interview
     */
    public function scheduleInterview(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'scheduled_at' => 'required|date',
            'meet_link' => 'nullable|string'
        ]);

        Interview::create($data);

        return response()->json(['message' => 'Interview scheduled']);
    }

    /**
     * Assign Beneficiary to Job
     */
    public function assignBeneficiary(Request $request)
    {
        $request->validate([
            'job_listing_id' => 'required|exists:job_listings,id',
            'beneficiary_id' => 'required|exists:beneficiaries,id',
        ]);

        $jobListing = JobListing::findOrFail($request->job_listing_id);

        if ($jobListing->assigned_beneficiary_id) {
            return response()->json(['error' => 'Job already has an assigned beneficiary'], 422);
        }

        $jobListing->update(['assigned_beneficiary_id' => $request->beneficiary_id]);

        Application::where('job_listing_id', $jobListing->id)
            ->where('beneficiary_id', $request->beneficiary_id)
            ->update(['status' => 'assigned']);

        activity()->log("PESO assigned beneficiary #{$request->beneficiary_id} to job #{$jobListing->id}");

        return response()->json(['success' => true, 'message' => 'Beneficiary assigned successfully']);
    }

    /**
     * Export DOLE Report (PDF or Excel)
     */
    public function exportDOLEReport(Request $request, ReportService $reports)
    {
        $format = $request->query('format', 'pdf');

        $data = Application::with(['beneficiary', 'jobListing', 'jobListing.employer'])->get();

        if ($format === 'excel') {
            return $reports->exportExcel($data, 'dole-report.xlsx');
        }

        return $reports->generateDOLEReport($data, 'reports.dole', 'dole-report.pdf');
    }

    /**
     * ==========================
     * Pending Beneficiaries Flow
     * ==========================
     */
    public function pendingBeneficiaries()
    {
        $beneficiaries = Beneficiary::with('user')
            ->whereNotNull('onboarding_completed_at')
            ->where('approved', false)
            ->get();

        return Inertia::render('PESO/PendingBeneficiaries', [
            'beneficiaries' => $beneficiaries,
            'canApprove' => Auth::user()->hasAnyRole(['PESO Admin', 'Admin']),
        ]);
    }

    public function approveBeneficiary($id)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['PESO Admin', 'Admin'])) {
            abort(403, 'Only PESO Admin or Admin can approve beneficiaries');
        }

        $beneficiary = Beneficiary::findOrFail($id);

        $beneficiary->update([
            'approved' => true,
            'approved_at' => now(),
            'approved_by' => $user->id,
        ]);

        return back()->with('success', 'Beneficiary approved successfully.');
    }

    public function rejectBeneficiary($id)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['PESO Admin', 'Admin'])) {
            abort(403, 'Only PESO Admin or Admin can reject beneficiaries');
        }

        $beneficiary = Beneficiary::findOrFail($id);

        $beneficiary->update([
            'approved' => false,
            'rejected_at' => now(),
            'approved_by' => $user->id,
        ]);

        return back()->with('error', 'Beneficiary rejected.');
    }

    /**
     * ==========================
     * Pending Employers Flow
     * ==========================
     */
  public function pendingEmployers()
{
    $employers = Employer::with('user') // load related User
        ->where('approved', false)
        ->get();

    return Inertia::render('PESO/Employers/Pending', [
        'employers' => $employers,
        'canApprove' => Auth::user()->hasAnyRole(['PESO Admin', 'Admin']),
    ]);
}

    

    public function approveEmployer($id)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['PESO Admin', 'Admin'])) {
            abort(403, 'Only PESO Admin or Admin can approve employers');
        }

        $employer = Employer::findOrFail($id);

        $employer->update([
            'approved' => true,
            'approved_at' => now(),
            'approved_by' => $user->id,
        ]);

        return back()->with('success', 'Employer approved successfully.');
    }

    public function rejectEmployer($id)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['PESO Admin', 'Admin'])) {
            abort(403, 'Only PESO Admin or Admin can reject employers');
        }

        $employer = Employer::findOrFail($id);

        $employer->update([
            'approved' => false,
            'rejected_at' => now(),
            'approved_by' => $user->id,
        ]);

        return back()->with('error', 'Employer rejected.');
    }
}
