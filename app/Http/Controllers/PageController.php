<?php

namespace App\Http\Controllers;
use App\Models\Attendance;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Foundation\Application;
use Inertia\Inertia;

class PageController extends Controller
{
    public function welcome()
    {
        return Inertia::render('Welcome', [
            'canLogin' => RouteFacade::has('login'),
            'canRegister' => true,
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }

    public function loginEmployer()
    {
        return Inertia::render('Auth/LoginEmployer');
    }

    public function loginPeso()
    {
        return Inertia::render('Auth/LoginPeso');
    }

    // Employer pages
    public function employerApplicants()
    {
        return Inertia::render('Employer/Applicants');
    }

    public function employerRecommended()
    {
        return Inertia::render('Employer/Recommended');
    }

    public function employerInterviews()
    {
        return Inertia::render('Employer/Schedule');
    }

    public function employerPerformance()
    {
        return Inertia::render('Employer/Performance');
    }

    public function employerWorkOutput()
    {
        return Inertia::render('Employer/WorkOutput');
    }

    public function employerReports()
    {
        return Inertia::render('Employer/Reports');
    }

public function employerAttendance()
{
    $records = Attendance::with('beneficiary')
        ->latest()
        ->get()
        ->map(function ($a) {

            return [
                'id' => $a->id,

                // ✅ safe even if relation is null
                'beneficiary_name' => trim(
    ($a->beneficiary->first_name ?? '') . ' ' . ($a->beneficiary->last_name ?? '')
) ?: 'N/A',

                'date' => $a->date,
                'time_in' => $a->time_in,
                'time_out' => $a->time_out,

                // ✅ correct storage path handling
              'proof' => $a->notes ? asset('storage/'.$a->notes) : null,    
            ];
        });

    return inertia('Employer/Attendance', [
        'records' => $records
    ]);
}

    // Beneficiary pages
    public function beneficiaryApplications()
    {
        return Inertia::render('Beneficiary/Applications');
    }

    public function beneficiaryUploadDocuments()
    {
        return Inertia::render('Beneficiary/UploadDocuments');
    }

    public function beneficiaryJobs()
    {
        return Inertia::render('Beneficiary/Jobs');
    }
}
