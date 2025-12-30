<?php

namespace App\Http\Controllers\PESO;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Models\JobListing;
use App\Services\ReportService;
use Illuminate\Http\Request;
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

        // Prevent re-assignment
        if ($jobListing->assigned_beneficiary_id) {
            return response()->json(['error' => 'Job already has an assigned beneficiary'], 422);
        }

        $jobListing->update(['assigned_beneficiary_id' => $request->beneficiary_id]);

        // Update related application status
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
}
