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
    $employer = auth()->user()?->employer;
    if (!$employer) {
        $employer = \App\Models\Employer::where('user_id', auth()->id())->first();
    }

    if (!$employer) {
        return inertia('Employer/Attendance', [
            'records' => [],
            'employerJobs' => []
        ]);
    }

    // Fetch all jobs posted by this employer
    $employerJobs = $employer->jobListings()->get();

    // Fetch all applications for this employer's jobs
    $jobIds = $employerJobs->pluck('id')->toArray();
    $applications = \App\Models\Application::whereIn('job_listing_id', $jobIds)
        ->with('jobListing')
        ->get();

    // Create a mapping of beneficiary_id to job info
    $beneficiaryJobMap = [];
    foreach ($applications as $app) {
        if (!isset($beneficiaryJobMap[$app->beneficiary_id])) {
            $beneficiaryJobMap[$app->beneficiary_id] = [
                'job_listing_id' => $app->job_listing_id,
                'job_title' => $app->jobListing?->title ?? 'Unknown Job',
            ];
        }
    }

    // Fetch attendance records for this employer
    $records = Attendance::whereIn('employer_id', [$employer->id, auth()->id()])
        ->with('beneficiary')
        ->latest()
        ->get()
        ->map(function ($a) use ($beneficiaryJobMap) {
            $jobInfo = $beneficiaryJobMap[$a->beneficiary_id] ?? null;

            return [
                'id' => $a->id,
                'beneficiary_name' => trim(
                    ($a->beneficiary->first_name ?? '') . ' ' . ($a->beneficiary->last_name ?? '')
                ) ?: 'N/A',
                'date' => $a->date,
                'time_in' => $a->time_in,
                'time_out' => $a->time_out,
                'job_listing_id' => $jobInfo['job_listing_id'] ?? null,
                'job_title' => $jobInfo['job_title'] ?? 'N/A',
                'has_application' => $jobInfo !== null,
                'proof' => $a->notes ? asset('storage/'.$a->notes) : null,
            ];
        });

    return inertia('Employer/Attendance', [
        'records' => $records,
        'employerJobs' => $employerJobs->map(fn($job) => [
            'id' => $job->id,
            'title' => $job->title,
        ])->toArray()
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
