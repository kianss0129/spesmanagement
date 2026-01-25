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

    public function approveBeneficiary($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);

        $beneficiary->update([
            'approved' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return back()->with('success', 'Beneficiary approved');
    }

    public function rejectBeneficiary($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);

        $beneficiary->update([
            'approved' => false,
            'rejected_at' => now(),
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
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return back()->with('success', 'Employer approved');
    }

    public function rejectEmployer($id)
    {
        $employer = Employer::findOrFail($id);

        $employer->update([
            'approved' => false,
            'rejected_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return back()->with('error', 'Employer rejected');
    }

    /**
     * ==========================
     * VIEW APPLICATIONS
     * ==========================
     */
    public function viewBeneficiaryApplications(Beneficiary $beneficiary)
    {
        $beneficiary->load([
            'user',
            'applications.jobListing.employer.user'
        ]);

        return Inertia::render('PESO/Beneficiaries/Applications', [
            'beneficiary' => $beneficiary,
            'applications' => $beneficiary->applications,
        ]);
    }

    public function viewEmployerApplications(Employer $employer)
    {
        $employer->load([
            'user',
            'jobListings.applications.beneficiary.user'
        ]);

        return Inertia::render('PESO/Employers/Applications', [
            'employer' => $employer,
            'jobListings' => $employer->jobListings,
        ]);
    }
}
