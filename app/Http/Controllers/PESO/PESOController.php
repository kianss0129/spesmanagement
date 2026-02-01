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
     * ==========================
     * PENDING BENEFICIARIES
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

    public function monitoring()
    {
        $beneficiaries = Beneficiary::with(['user', 'applications.jobListing.employer'])
            ->where('approved', true)
            ->get()
            ->map(function ($beneficiary) {
                $latestApplication = $beneficiary->applications->sortByDesc('created_at')->first();
                $status = $latestApplication ? $latestApplication->status : 'No Application';
                $assignedEmployer = $latestApplication && $latestApplication->jobListing ? $latestApplication->jobListing->employer->company_name : null;

                return [
                    'id' => $beneficiary->id,
                    'name' => $beneficiary->name,
                    'status' => $status,
                    'assigned_employer' => $assignedEmployer,
                ];
            });

        return response()->json($beneficiaries);
    }

    public function approveBeneficiary($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);

        $beneficiary->update([
            'approved' => true,
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // Assign beneficiary role to the user
        $beneficiary->user()->update(['role' => 'beneficiary']);

        return back()->with('success', 'Beneficiary approved');
    }

    public function rejectBeneficiary($id, Request $request)
    {
        $beneficiary = Beneficiary::findOrFail($id);

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $beneficiary->update([
            'approved' => false,
            'approval_status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => Auth::id(),
        ]);

        return back()->with('error', 'Beneficiary rejected');
    }

    /**
     * ==========================
     * PENDING EMPLOYERS
     * ==========================
     */
    public function pendingEmployers()
    {
        $employers = Employer::with('user')
            ->where('approved', false)
            ->get();

        return Inertia::render('PESO/Employers/Pending', [
            'employers' => $employers,
            'canApprove' => Auth::user()->hasAnyRole(['PESO Admin', 'Admin']),
        ]);
    }

    public function approveEmployer($id)
    {
        $employer = Employer::findOrFail($id);

        $employer->update([
            'approved' => true,
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // Assign employer role to the user
        $employer->user()->update(['role' => 'employer']);

        return back()->with('success', 'Employer approved');
    }

    public function rejectEmployer($id, Request $request)
    {
        $employer = Employer::findOrFail($id);

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $employer->update([
            'approved' => false,
            'approval_status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => Auth::id(),
        ]);

        return back()->with('error', 'Employer rejected');
    }

    /**
     * ==========================
     * ONBOARDING VERIFICATION
     * ==========================
     */
    public function viewBeneficiaryApplications(Beneficiary $beneficiary)
    {
        // This now shows onboarding verification instead of job applications
        $beneficiary->load('user', 'school');

        return Inertia::render('PESO/Beneficiaries/Applications', [
            'beneficiary' => $beneficiary,
            'documents' => $beneficiary->documents ?? [],
            'submission_date' => $beneficiary->onboarding_completed_at,
            'approval_status' => $beneficiary->approval_status,
            'rejection_reason' => $beneficiary->rejection_reason,
        ]);
    }

    public function viewEmployerApplications(Employer $employer)
    {
        // This now shows onboarding verification instead of job applications
        $employer->load('user');

        return Inertia::render('PESO/Employers/Applications', [
            'employer' => $employer,
            'documents' => $employer->documents ?? [],
            'submission_date' => $employer->onboarding_completed_at,
            'company_details' => [
                'company_name' => $employer->company_name,
                'contact_person' => $employer->contact_person,
                'email' => $employer->email,
                'phone' => $employer->phone,
                'address' => $employer->address,
            ],
            'approval_status' => $employer->approval_status,
            'rejection_reason' => $employer->rejection_reason,
        ]);
    }

    public function jobListings()
    {
        $jobListings = JobListing::with(['employer', 'applications'])
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'employer_name' => $job->employer->company_name,
                    'applications_count' => $job->applications->count(),
                    'status' => $job->status,
                ];
            });

        return response()->json($jobListings);
    }
}
