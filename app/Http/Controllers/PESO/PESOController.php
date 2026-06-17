<?php


namespace App\Http\Controllers\PESO;
use App\Mail\EmployerApprovedMail;
use App\Mail\BeneficiaryRejected;
use Illuminate\Support\Facades\Mail;
use App\Mail\BeneficiaryApprovedMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Models\JobListing;
use App\Models\Beneficiary;
use App\Models\Employer;
use App\Models\EmployerRating;
use App\Models\EmployerCompliance;
use App\Models\Attendance;
use App\Models\WorkOutput;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Announcement;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Models\Report;
use App\Models\User;
use App\Mail\EmployerRejectedMail;


class PESOController extends Controller
{
public function index()
{
    // Get latest application per beneficiary
    $applications = \App\Models\Application::with([
        'beneficiary',
        'jobListing.employer'
    ])
    ->latest()
    ->get()
    ->groupBy('beneficiary_id')
    ->map(function ($items) {
        return $items->first(); // latest application only
    })
    ->values()
    ->map(function ($app) {
        return [
            'id' => $app->id,
            'beneficiary_name' => trim(
                ($app->beneficiary->first_name ?? '') . ' ' .
                ($app->beneficiary->last_name ?? '')
            ) ?: 'N/A',
            'applicant_name' => trim(
                ($app->beneficiary->first_name ?? '') . ' ' .
                ($app->beneficiary->last_name ?? '')
            ) ?: 'N/A',

            'job_title' => $app->jobListing?->title ?? 'N/A',

            'employer_name' => $app->jobListing?->employer?->company_name ?? 'N/A',

            'status' => $app->status,
            'created_at' => optional($app->created_at)->toISOString(),

            'beneficiary_id' => $app->beneficiary_id,

            'is_real_application' => true,
        ];
    });

    // Approved beneficiaries without any application
    $allApplicationBeneficiaryIds = \App\Models\Application::pluck('beneficiary_id')
        ->unique();

    $unassignedBeneficiaries = \App\Models\Beneficiary::with('user')
        ->where('approved', true)
        ->whereNotIn('id', $allApplicationBeneficiaryIds)
        ->get()
        ->map(function ($beneficiary) {
            return [
                'id' => 'unassigned_' . $beneficiary->id,

                'beneficiary_name' => trim(
                    ($beneficiary->first_name ?? '') . ' ' .
                    ($beneficiary->last_name ?? '')
                ) ?: ($beneficiary->user->name ?? 'N/A'),
                'applicant_name' => trim(
                    ($beneficiary->first_name ?? '') . ' ' .
                    ($beneficiary->last_name ?? '')
                ) ?: ($beneficiary->user->name ?? 'N/A'),

                'job_title' => null,

                'employer_name' => null,

                'status' => 'unassigned',
                'created_at' => optional($beneficiary->created_at)->toISOString(),

                'beneficiary_id' => $beneficiary->id,

                'is_real_application' => false,
            ];
        });

    return $applications
        ->concat($unassignedBeneficiaries)
        ->values();
}

public function applicationsForInterview()
{
    return Application::with([
            'beneficiary',
            'jobListing.employer',
            'interview',
        ])
        ->where('status', 'for_interview')
        ->whereDoesntHave('interview', function ($query) {
            $query->whereIn('result', ['passed', 'failed']);
        })
        ->whereDoesntHave('interview', function ($query) {
            $query->where('status', 'scheduled');
        })
        ->latest()
        ->get()
        ->map(function ($app) {
            return [
                'id' => $app->id,
                'beneficiary_name' => trim(
                    ($app->beneficiary->first_name ?? '') . ' ' .
                    ($app->beneficiary->last_name ?? '')
                ) ?: 'N/A',
                'applicant_name' => trim(
                    ($app->beneficiary->first_name ?? '') . ' ' .
                    ($app->beneficiary->last_name ?? '')
                ) ?: 'N/A',
                'job_title' => $app->jobListing?->title ?? 'N/A',
                'employer_name' => $app->jobListing?->employer?->company_name ?? 'N/A',
                'status' => $app->status,
                'category' => $app->beneficiary?->category ?? $app->beneficiary?->user?->beneficiary_type ?? null,
                'beneficiary_type' => $app->beneficiary?->category ?? $app->beneficiary?->user?->beneficiary_type ?? null,
                'created_at' => optional($app->created_at)->toISOString(),
                'beneficiary_id' => $app->beneficiary_id,
                'interview_status' => $app->interview?->status,
                'interview_result' => $app->interview?->result,
                'is_real_application' => true,
            ];
        })
        ->values();
}


public function getReports()
{
    $user = auth()->user();

    $reports = Report::query();

    if ($user && $user->hasRole('Employer')) {
        $employer = \App\Models\Employer::where('user_id', $user->id)->first();
        $reports->where('employer_id', $employer?->id);
    }

    return $reports->with('employer')->get()->map(function ($report) {
        $employerName = $report->employer?->company_name;

        if (! $employerName && $report->employer_id) {
            $employerName = Employer::where('user_id', $report->employer_id)
                ->value('company_name');
        }

        return [
            'id' => $report->id,
            'title' => $report->title,
            'body' => $report->body,
            'employer_id' => $report->employer_id,
            'employer_name' => $employerName,
            'created_at' => $report->created_at,
            'file_path' => $report->file_path,
            'file_url' => $report->file_path ? Storage::url($report->file_path) : null,
        ];
    });
}
public function employerCompliancePage()
{
    $employers = Employer::select('id', 'company_name')
        ->where('approved', true)
        ->get();

    return Inertia::render('PESO/EmployerCompliance', [
        'employers' => $employers
    ]);
}








public function profile($id)
{
  $beneficiary = Beneficiary::with('employer', 'user')->findOrFail($id);


return Inertia::render('PESO/BeneficiaryProfile', [
    'beneficiary' => [
        'id' => $beneficiary->id,


        // FIX: name usually from user table
        'name' => $beneficiary->user->name ?? 'N/A',
        'email' => $beneficiary->user->email ?? 'N/A',


        'phone' => $beneficiary->phone ?? null,
        'address' => $beneficiary->address ?? null,


        // IMPORTANT: consistent status
        'approval_status' => strtolower($beneficiary->approval_status ?? 'pending'),


        // employment
        'employment_status' => $beneficiary->status ?? 'pending',
        'job_title' => $beneficiary->job_title ?? null,


        'employer' => $beneficiary->employer ? [
            'company_name' => $beneficiary->employer->company_name,
        ] : null,
    ]
]);
}
public function documents($id)
{
    $beneficiary = \App\Models\Beneficiary::findOrFail($id);


    $existing = $beneficiary->documents ?? [];


    if (is_string($existing)) {
        $existing = json_decode($existing, true) ?: [];
    }


    $fields = [
        'birth_certificate',
        'school_record',
        'osy_certificate',
        'income_proof',
        'displacement_certificate',
        'termination_notice'
    ];


    $documents = [];


    foreach ($fields as $field) {
        $documents[] = [
            'name' => ucwords(str_replace('_',' ',$field)),
            'status' => isset($existing[$field]) ? 'uploaded' : 'pending',
            'path' => $existing[$field] ?? null
        ];
    }


    return \Inertia\Inertia::render('PESO/Documents', [
        'beneficiary' => $beneficiary,
        'documents' => $documents
    ]);
}
public function viewJobApplications($jobId)
{
    $job = \App\Models\JobListing::with('applications.beneficiary.user')->findOrFail($jobId);


    return Inertia::render('PESO/JobApplications', [
        'job' => $job,
        'applications' => $job->applications
    ]);
}  




 public function getEmployerCompliance()
    {
        return Employer::where('approved', true)
            ->select('id', 'company_name', 'email', 'status', 'approved_at')
            ->get();
    }


    public function getBeneficiaryRatings()
    {
        return EmployerRating::with('beneficiary','rater')->get();
    }


    public function getTopBeneficiaries()
    {
        return EmployerRating::with('beneficiary')
            ->orderByDesc('overall')
            ->take(10)
            ->get();
    }


    public function getApplicantTrends()
    {
        return Application::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year','month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }


    public function getCompletionRate()
    {
        return Beneficiary::selectRaw('YEAR(created_at) as batch_year, COUNT(*) as applicants')
            ->groupBy('batch_year')
            ->get();
    }


    public function getAttendance()
    {
        return Attendance::with(['beneficiary.user', 'beneficiary.school', 'employer'])->get();
    }


    public function getEmployerReliability()
    {
        return Application::join('job_listings', 'applications.job_listing_id', '=', 'job_listings.id')
            ->join('employers', 'job_listings.employer_id', '=', 'employers.id')
            ->where('applications.status', 'completed')
            ->selectRaw('job_listings.employer_id, employers.company_name as employer_name, COUNT(*) as completed_count, COUNT(DISTINCT job_listing_id) as job_listing_count')
            ->groupBy('job_listings.employer_id', 'employers.company_name')
            ->orderByDesc('completed_count')
            ->get();
    }


    public function getInterviewHistory()
    {
        return Interview::with('beneficiary','employer')->get();
    }


    public function generateDOLEReport()
    {
        $beneficiaries = Beneficiary::with('user')->get();
        $employers = Employer::with('user')->get();


        return response()->json([
            'beneficiaries'=>$beneficiaries,
            'employers'=>$employers
        ]);
    }

    public function exportDOLEReport()
    {
        $beneficiaries = Beneficiary::with([
            'user',
            'employer',
            'job',
            'applications.jobListing.employer',
        ])
            ->withCount([
                'attendances as attendance_count',
                'workHistory as daily_report_count',
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $headers = [
            'Beneficiary Name',
            'Category',
            'Employer',
            'Job',
            'Application Status',
            'Attendance Count',
            'Daily Report Count',
            'Completion Status',
        ];

        $filename = 'dole-spes-report-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($beneficiaries, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($beneficiaries as $beneficiary) {
                $latestApplication = $beneficiary->applications
                    ->sortByDesc('created_at')
                    ->first();

                $employerName = $latestApplication?->jobListing?->employer?->company_name
                    ?? $beneficiary->employer?->company_name
                    ?? 'N/A';

                $jobTitle = $latestApplication?->jobListing?->title
                    ?? $beneficiary->job?->title
                    ?? 'N/A';

                fputcsv($handle, [
                    $beneficiary->full_name ?: ($beneficiary->user?->name ?? 'N/A'),
                    $beneficiary->category ?? 'N/A',
                    $employerName,
                    $jobTitle,
                    $latestApplication?->status ?? 'No Application',
                    $beneficiary->attendance_count,
                    $beneficiary->daily_report_count,
                    $beneficiary->employment_status
                        ?? ($latestApplication?->status === 'completed' ? 'completed' : 'not completed'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }


 public function notifications()
{
    $announcements = Announcement::latest()->get();


    return Inertia::render('PESO/Notifications', [
        'announcements' => $announcements
    ]);
}






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
        ->whereNotNull('submitted_at')
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
                $readiness = $this->completionReadiness($beneficiary, $latestApplication);


                return [
                    'id' => $beneficiary->id,
                    'name' => $beneficiary->user?->name ?? 'N/A',
                    'status' => $status,
                    'approval_status' => $beneficiary->approval_status ?? 'approved',
                    'application_id' => $latestApplication?->id,
                    'application_status' => $latestApplication?->status,
                    'job_title' => $latestApplication?->jobListing?->title,
                    'employer_id' => $latestApplication?->jobListing?->employer?->id,
                    'job_listing_id' => $latestApplication?->job_listing_id,
                    'assigned_employer' => $assignedEmployer,
                    'completion_readiness' => $readiness,
                ];
            });


        return response()->json($beneficiaries);
    }


   public function approveBeneficiary($id)
{
    $beneficiary = Beneficiary::with('user')->findOrFail($id);

    // Generate temporary password
    $temporaryPassword = Str::random(10);

    // Update user password
    if ($beneficiary->user) {
        $beneficiary->user->update([
            'password' => Hash::make($temporaryPassword),
            'role' => 'beneficiary',
        ]);
    }

    // Approve beneficiary
    $beneficiary->update([
        'approved' => true,
        'approval_status' => 'approved',
        'approved_at' => now(),
        'approved_by' => Auth::id(),
    ]);

    Application::where('beneficiary_id', $beneficiary->id)
        ->whereIn('status', ['qualified', 'for_approval', 'passed'])
        ->latest()
        ->first()
        ?->update(['status' => 'approved']);

    // When admin approves, screening is complete — advance to 'for_exam'
    Application::where('beneficiary_id', $beneficiary->id)
        ->whereIn('status', ['applied', 'screening'])
        ->update(['status' => 'for_exam']);

    try {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($beneficiary)
            ->withProperties([
                'module' => 'Beneficiary',
                'user_id' => $beneficiary->user?->id,
                'status' => 'approved',
            ])
            ->log('Beneficiary application approved');
    } catch (\Throwable $e) {
        report($e);
    }

    // Send email notification
    Mail::to($beneficiary->email)->send(
        new BeneficiaryApprovedMail(
            $beneficiary->full_name,
            $temporaryPassword
        )
    );

    return back()->with('success', 'Beneficiary approved and email sent.');
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

    Application::where('beneficiary_id', $beneficiary->id)
        ->latest()
        ->first()
        ?->update(['status' => 'rejected']);

    // ✅ SEND EMAIL
    try {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($beneficiary)
            ->withProperties([
                'module' => 'Beneficiary',
                'user_id' => $beneficiary->user?->id,
                'status' => 'rejected',
            ])
            ->log('Beneficiary application rejected');
    } catch (\Throwable $e) {
        report($e);
    }

Mail::to($beneficiary->email)->send(
    new BeneficiaryRejected($beneficiary)
);

    return back()->with('error', 'Beneficiary rejected');
}

public function requestBeneficiaryCorrection($id, Request $request)
{
    $beneficiary = Beneficiary::findOrFail($id);

    $validated = $request->validate([
        'correction_remarks' => 'required|string|max:1000',
    ]);

    $beneficiary->update([
        'approved' => false,
        'approval_status' => 'needs_correction',
        'status' => 'needs_correction',
        'rejection_reason' => $validated['correction_remarks'],
        'approved_by' => Auth::id(),
    ]);

    Application::where('beneficiary_id', $beneficiary->id)
        ->latest()
        ->first()
        ?->update(['status' => 'needs_correction']);

    try {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($beneficiary)
            ->withProperties([
                'module' => 'Requirements',
                'user_id' => $beneficiary->user?->id,
                'status' => 'needs_correction',
            ])
            ->log('Requirement correction requested');
    } catch (\Throwable $e) {
        report($e);
    }

    return back()->with('success', 'Correction request sent to beneficiary.');
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
    $employer = Employer::with('user')->findOrFail($id);

    $employer->update([
        'approved' => true,
        'approval_status' => 'approved',
        'approved_at' => now(),
        'approved_by' => Auth::id(),
    ]);

    // Assign employer role
    $employer->user()->update([
        'role' => 'employer'
    ]);

    // ✅ SEND EMAIL
    try {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($employer)
            ->withProperties([
                'module' => 'Employer',
                'user_id' => $employer->user?->id,
                'status' => 'approved',
            ])
            ->log('Employer application approved');
    } catch (\Throwable $e) {
        report($e);
    }

    Mail::to($employer->user->email)->send(
        new EmployerApprovedMail($employer)
    );

    return back()->with('success', 'Employer approved');
}


   public function rejectEmployer($id, Request $request)
{
    $employer = Employer::with('user')->findOrFail($id);

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

    // ✅ SEND EMAIL
    try {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($employer)
            ->withProperties([
                'module' => 'Employer',
                'user_id' => $employer->user?->id,
                'status' => 'rejected',
            ])
            ->log('Employer application rejected');
    } catch (\Throwable $e) {
        report($e);
    }

    Mail::to($employer->user->email)->send(
        new \App\Mail\EmployerRejectedMail($employer)
    );

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
        $latestApplication = Application::with('jobListing.employer')
            ->where('beneficiary_id', $beneficiary->id)
            ->latest()
            ->first();

        $completionReadiness = $this->completionReadiness($beneficiary, $latestApplication);


        // Build documents array from stored file paths (if any)
        $documents = [];
        if ($beneficiary->documents) {
            $raw = is_array($beneficiary->documents) ? $beneficiary->documents : [$beneficiary->documents];
            foreach ($raw as $entry) {
                // Entry may be a plain path string, or an array/object with keys like 'path','name','uploaded_at'
                $entryPath = null;
                $entryName = null;
                $entryUploadedAt = null;


                if (is_string($entry)) {
                    $entryPath = $entry;
                } elseif (is_array($entry)) {
                    $entryPath = $entry['path'] ?? $entry['file'] ?? null;
                    $entryName = $entry['name'] ?? $entry['filename'] ?? null;
                    $entryUploadedAt = $entry['uploaded_at'] ?? $entry['created_at'] ?? null;
                } elseif (is_object($entry)) {
                    $entryPath = $entry->path ?? $entry->file ?? null;
                    $entryName = $entry->name ?? $entry->filename ?? null;
                    $entryUploadedAt = $entry->uploaded_at ?? $entry->created_at ?? null;
                }


                if (! $entryPath) {
                    // skip malformed entry
                    continue;
                }


                // Check if file exists on public disk
                $exists = Storage::disk('public')->exists((string) $entryPath);


                // Build URL for storage path using Storage disk
                $url = null;
                if ($exists) {
                    try {
                        $url = Storage::disk('public')->url((string) $entryPath);
                    } catch (\Throwable $e) {
                        $url = null;
                    }
                }


                // Determine uploaded at timestamp from file system if not provided
                $uploadedAt = $entryUploadedAt;
                if (! $uploadedAt && $exists) {
                    try {
                        $fullPath = storage_path('app/public/' . ltrim((string) $entryPath, '/'));
                        if (file_exists($fullPath)) {
                            $uploadedAt = date('c', filemtime($fullPath));
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }


                $documents[] = [
                    'path' => $entryPath,
                    'url' => $url,
                    'name' => $entryName ?? basename((string) $entryPath),
                    'uploaded_at' => $uploadedAt,
                    'exists' => $exists,
                ];
            }
        }


        return Inertia::render('PESO/Beneficiaries/Applications', [
            'beneficiary' => $beneficiary,
            'documents' => $documents,
            'submission_date' => $beneficiary->onboarding_completed_at,
            'approval_status' => $beneficiary->approval_status ?? 'pending',
            'rejection_reason' => $beneficiary->rejection_reason,
            'beneficiary_type' => $beneficiary->user->beneficiary_type ?? 'student',
            'latest_application' => $latestApplication ? $this->formatCompletionApplication($latestApplication) : null,
            'completion_readiness' => $completionReadiness,
        ]);
    }


    public function viewEmployerApplications(Employer $employer)
    {
        // This now shows onboarding verification instead of job applications
        $employer->load('user');


        // Build documents array from stored file paths (if any)
        $documents = [];
        if ($employer->documents) {
            $raw = is_array($employer->documents) ? $employer->documents : [$employer->documents];
            foreach ($raw as $entry) {
                // Entry may be a plain path string, or an array/object with keys like 'path','name','uploaded_at'
                $entryPath = null;
                $entryName = null;
                $entryUploadedAt = null;


                if (is_string($entry)) {
                    $entryPath = $entry;
                } elseif (is_array($entry)) {
                    $entryPath = $entry['path'] ?? $entry['file'] ?? null;
                    $entryName = $entry['name'] ?? $entry['filename'] ?? null;
                    $entryUploadedAt = $entry['uploaded_at'] ?? $entry['created_at'] ?? null;
                } elseif (is_object($entry)) {
                    $entryPath = $entry->path ?? $entry->file ?? null;
                    $entryName = $entry->name ?? $entry->filename ?? null;
                    $entryUploadedAt = $entry->uploaded_at ?? $entry->created_at ?? null;
                }


                if (! $entryPath) {
                    // skip malformed entry
                    continue;
                }


                // Check if file exists on public disk
                $exists = Storage::disk('public')->exists((string) $entryPath);


                // Build URL for storage path using Storage disk
                $url = null;
                if ($exists) {
                    try {
                        $url = Storage::disk('public')->url((string) $entryPath);
                    } catch (\Throwable $e) {
                        $url = null;
                    }
                }


                // Determine uploaded at timestamp from file system if not provided
                $uploadedAt = $entryUploadedAt;
                if (! $uploadedAt && $exists) {
                    try {
                        $fullPath = storage_path('app/public/' . ltrim((string) $entryPath, '/'));
                        if (file_exists($fullPath)) {
                            $uploadedAt = date('c', filemtime($fullPath));
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }


                $documents[] = [
                    'path' => $entryPath,
                    'url' => $url,
                    'name' => $entryName ?? basename((string) $entryPath),
                    'uploaded_at' => $uploadedAt,
                    'exists' => $exists,
                ];
            }
        }


        return Inertia::render('PESO/Employers/Applications', [
            'employer' => $employer,
            'documents' => $documents,
            'submission_date' => $employer->onboarding_completed_at,
            'company_details' => [
                'company_name' => $employer->company_name ?? 'N/A',
                'contact_person' => $employer->contact_person ?? 'N/A',
                'email' => $employer->email ?? 'N/A',
                'phone' => $employer->phone ?? 'N/A',
                'address' => $employer->address ?? 'N/A',
            ],
            'approval_status' => $employer->approval_status ?? 'pending',
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
                    'slots' => $job->slots,
                    'closing_date' => $job->closing_date,
                    'applications_count' => $job->applications->count(),
                    'status' => $job->status,
                ];
            });


        return response()->json($jobListings);
    }


 public function assignBeneficiary(Request $request)
{
    if (! Auth::user()?->hasAnyRole(['Super Admin', 'Admin', 'PESO Admin'])) {
        abort(403, 'Only Admin or CPESO Admin users can assign beneficiaries.');
    }

    $jobListingId = $request->input('job_listing_id', $request->input('job_id'));
    $request->merge(['job_listing_id' => $jobListingId]);

    $request->validate([
        'beneficiary_id' => 'required|exists:beneficiaries,id',
        'job_listing_id' => 'required|exists:job_listings,id',
    ]);


    $beneficiary = Beneficiary::findOrFail($request->beneficiary_id);
    $job = JobListing::findOrFail($request->job_listing_id);


    if (!$beneficiary->approved) {
        return response()->json([
            'message' => 'Beneficiary is not approved.'
        ], 422);
    }

    $hasActiveJob = Application::where('beneficiary_id', $beneficiary->id)
        ->where('status', '!=', 'completed')
        ->whereNotNull('job_listing_id')
        ->where('job_listing_id', '!=', $job->id)
        ->exists();

    if ($hasActiveJob) {
        return response()->json([
            'message' => 'This beneficiary already has an active job assignment. Complete the current job before assigning a new one.'
        ], 422);
    }


    // ✅ Save application
    Application::updateOrCreate(
        [
            'beneficiary_id' => $beneficiary->id,
            'job_listing_id' => $job->id,
        ],
        [
            'status' => 'assigned'
        ]
    );


    $beneficiary->employer_id = $job->employer_id;
    $beneficiary->job_id = $job->id;
    $beneficiary->employment_status = 'active';
    $beneficiary->save();

    try {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($beneficiary)
            ->withProperties([
                'module' => 'Placement',
                'user_id' => $beneficiary->user?->id,
                'status' => 'assigned',
            ])
            ->log('Beneficiary assigned to employer');
    } catch (\Throwable $e) {
        report($e);
    }


    return response()->json(['message' => 'Beneficiary assigned successfully.']);
}

public function markApplicationQualified(Application $application)
{
    if ($application->status !== 'interview_passed') {
        return response()->json([
            'message' => 'Only applications with a passed interview can be marked as qualified.',
        ], 422);
    }

    $application->update([
        'status' => 'qualified',
    ]);

    return response()->json([
        'message' => 'Application marked as qualified.',
        'application' => $application->fresh(['beneficiary', 'jobListing.employer']),
    ]);
}

public function deployApplication(Application $application)
{
    if ($application->status !== 'contract_signed') {
        return response()->json([
            'message' => 'Only applications with a signed contract can be marked as deployed.',
        ], 422);
    }

    $application->update([
        'status' => 'deployed',
    ]);

    return response()->json([
        'message' => 'Application marked as deployed.',
        'application' => $application->fresh(['beneficiary', 'jobListing.employer']),
    ]);
}

public function approveCompletion(Request $request, Application $application)
{
    $validated = $request->validate([
        'remarks' => 'nullable|string|max:1000',
    ]);

    if (! in_array($application->status, ['completion_review', 'deployed', 'ongoing'], true)) {
        return response()->json([
            'message' => 'Only deployed, ongoing, or completion review applications can be completed.',
        ], 422);
    }

    $application->load('beneficiary');

    $application->status = 'completed';

    if (array_key_exists('remarks', $validated) && Schema::hasColumn('applications', 'remarks')) {
        $application->remarks = $validated['remarks'];
    }

    $application->save();

    if ($application->beneficiary && Schema::hasColumn('beneficiaries', 'completed_at')) {
        $application->beneficiary->forceFill([
            'completed_at' => now(),
        ])->save();
    }

    if ($application->beneficiary && Schema::hasColumn('beneficiaries', 'employment_status')) {
        $application->beneficiary->forceFill([
            'employment_status' => 'completed',
        ])->save();
    }

    try {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($application)
            ->withProperties([
                'module' => 'Beneficiary',
                'user_id' => $application->beneficiary?->user_id,
                'status' => 'completed',
            ])
            ->log('Beneficiary completion approved');
    } catch (\Throwable $e) {
        report($e);
    }

    return response()->json([
        'message' => 'Application completion approved.',
        'application' => $this->formatCompletionApplication($application->fresh('jobListing.employer')),
    ]);
}

private function completionReadiness(Beneficiary $beneficiary, ?Application $application): array
{
    if (! $application) {
        return [
            'has_dtr' => false,
            'has_approved_daily_reports' => false,
            'has_employer_rating' => false,
            'has_certificate' => false,
        ];
    }

    return [
        'has_dtr' => Attendance::where('beneficiary_id', $beneficiary->id)
            ->where(function ($query) use ($application) {
                $query->where('application_id', $application->id)
                    ->orWhereNull('application_id');
            })
            ->exists(),
        'has_approved_daily_reports' => WorkOutput::where('beneficiary_id', $beneficiary->id)
            ->where(function ($query) use ($application) {
                $query->where('application_id', $application->id)
                    ->orWhereNull('application_id');
            })
            ->where('status', 'approved')
            ->exists(),
        'has_employer_rating' => EmployerRating::where('beneficiary_id', $beneficiary->id)
            ->where('application_id', $application->id)
            ->exists(),
        'has_certificate' => filled($application->certificate_path),
    ];
}

private function formatCompletionApplication(Application $application): array
{
    return [
        'id' => $application->id,
        'status' => $application->status,
        'job_title' => $application->jobListing?->title,
        'employer_name' => $application->jobListing?->employer?->company_name,
        'certificate_path' => $application->certificate_path,
        'created_at' => optional($application->created_at)->toISOString(),
        'updated_at' => optional($application->updated_at)->toISOString(),
    ];
}






// ==========================
// UNDO BENEFICIARY APPROVAL
// ==========================
public function undoApproveBeneficiary($id)
{
    $beneficiary = Beneficiary::findOrFail($id);


    $beneficiary->update([
        'approved' => false,
        'approval_status' => 'pending',
        'approved_at' => null,
        'approved_by' => null,
    ]);



    return back()->with('success', 'Beneficiary approval undone');
}




// ==========================
// UNDO EMPLOYER APPROVAL
// ==========================
public function undoApproveEmployer($id)
{
    $employer = Employer::findOrFail($id);


    $employer->update([
        'approved' => false,
        'approval_status' => 'pending',
        'approved_at' => null,
        'approved_by' => null,
    ]);


    return back()->with('success', 'Employer approval undone');
}


/**
 * ==========================
 * APPROVED BENEFICIARIES
 * ==========================
 */
public function approvedBeneficiaries()
{
    $beneficiaries = Beneficiary::with([
            'user',
            'employer',
            'job',
            'applications.jobListing.employer',
        ])
        ->where('approved', true)
        ->get()
        ->map(function ($b) {
            $latestApplication = $b->applications->sortByDesc('created_at')->first();
            $jobListing = $b->job ?? $latestApplication?->jobListing;
            $employer = $b->employer ?? $jobListing?->employer ?? $latestApplication?->jobListing?->employer;
            $completionStatus = $b->completed_at
                ? 'completed'
                : ($b->employment_status ?? $latestApplication?->status);

            return [
                'id' => $b->id,
                'name' => $b->user?->name ?? $b->name ?? 'N/A',
                'email' => $b->user?->email ?? 'N/A',
                'category' => $b->category ?? $b->user?->beneficiary_type,
                'beneficiary_type' => $b->category ?? $b->user?->beneficiary_type,
                'approved_at' => $b->approved_at?->format('Y-m-d H:i') ?? 'N/A',
                'approval_status' => $b->approval_status ?? 'approved',
                'status' => $b->status,
                'employer_id' => $b->employer_id ?? $employer?->id,
                'job_id' => $b->job_id,
                'job_listing_id' => $jobListing?->id ?? $latestApplication?->job_listing_id,
                'assigned_employer' => $employer?->company_name,
                'employer_name' => $employer?->company_name,
                'job_title' => $jobListing?->title,
                'employment_status' => $b->employment_status,
                'completion_status' => $completionStatus,
                'application_status' => $latestApplication?->status,
                'completed_at' => $b->completed_at?->format('Y-m-d H:i'),
            ];
        });


    return response()->json($beneficiaries);
}


/**
 * ==========================
 * APPROVED EMPLOYERS
 * ==========================
 */
public function approvedEmployers()
{
    $assignedApplicationStatuses = [
        'assigned',
        'for_contract',
        'contract_signed',
        'deployed',
        'ongoing',
        'completion_review',
        'completed',
    ];

    $activeJobStatuses = ['active', 'open', 'approved', 'published'];
    $pendingJobStatuses = ['pending', 'pending_review', 'for_review'];

    $employers = Employer::with(['user', 'jobListings.applications'])
        ->where('approved', true)
        ->get()
        ->map(function ($e) use ($assignedApplicationStatuses, $activeJobStatuses, $pendingJobStatuses) {
            $jobListings = $e->jobListings;
            $applicationBeneficiaryIds = $jobListings
                ->flatMap->applications
                ->filter(fn($application) => in_array($application->status, $assignedApplicationStatuses, true))
                ->pluck('beneficiary_id');

            $directBeneficiaryIds = Beneficiary::where('employer_id', $e->id)->pluck('id');
            $assignedBeneficiariesCount = $directBeneficiaryIds
                ->merge($applicationBeneficiaryIds)
                ->filter()
                ->unique()
                ->count();

            $activeJobPosts = $jobListings
                ->filter(function ($job) use ($activeJobStatuses) {
                    $status = strtolower(trim((string) $job->status));

                    return $status === '' || in_array($status, $activeJobStatuses, true);
                })
                ->count();

            $pendingJobsCount = $jobListings
                ->filter(fn($job) => in_array(strtolower((string) $job->status), $pendingJobStatuses, true))
                ->count();

            return [
                'id' => $e->id,
                'company_name' => $e->company_name,
                'email' => $e->user?->email ?? $e->email ?? 'N/A',
                'contact_person' => $e->contact_person ?? 'N/A',
                'approved_at' => $e->approved_at?->format('Y-m-d H:i') ?? 'N/A',
                'approval_status' => $e->approval_status ?? 'approved',
                'status' => $e->status,
                'job_listings_count' => $jobListings->count(),
                'active_job_posts' => $activeJobPosts,
                'active_jobs_count' => $activeJobPosts,
                'pending_jobs_count' => $pendingJobsCount,
                'pending_job_posts' => $pendingJobsCount,
                'assigned_beneficiaries_count' => $assignedBeneficiariesCount,
                'assigned_beneficiaries' => $assignedBeneficiariesCount,
            ];
        });


   return response()->json($employers);
}


public function revertBeneficiary($id)
{
    $beneficiary = Beneficiary::findOrFail($id);


    $beneficiary->update([
        'approved' => false,
        'approval_status' => 'pending',
        'status' => 'Pending', // 🔥 IMPORTANT
        'approved_at' => null,
        'approved_by' => null,
    ]);


    if ($beneficiary->user) {
        $beneficiary->user->update([
            'status' => 'pending'
        ]);
    }


    return response()->json(['success' => true]);
}


public function revertEmployer($id)
{
    $employer = \App\Models\Employer::findOrFail($id);


    $employer->update([
        'approved' => false, // ⭐ ITO ANG PINAKA-IMPORTANT
        'approval_status' => 'pending',
        'approved_at' => null,
        'approved_by' => null,
    ]);


    activity()
        ->causedBy(auth()->user())
        ->log('Reverted employer ID ' . $id . ' to Pending');


    return response()->json([
        'message' => 'Employer reverted successfully'
    ]);
}


public function storeReport(Request $request)
{
    $request->validate([
        'title' => 'required',
        'body' => 'required',
    ]);


    Report::create([
        'user_id' => auth()->id(),
        'title' => $request->title,
        'body' => $request->body,
    ]);


    return response()->json([
        'message' => 'Report sent to PESO successfully'
    ]);
}

public function auditTrail()
{
    if (! Schema::hasTable('activity_log')) {
        return response()->json([]);
    }

    $activities = DB::table('activity_log')
        ->leftJoin('users', function ($join) {
            $join->on('activity_log.causer_id', '=', 'users.id')
                ->where('activity_log.causer_type', '=', User::class);
        })
        ->select(
            'activity_log.id',
            'activity_log.log_name',
            'activity_log.description',
            'activity_log.subject_type',
            'activity_log.subject_id',
            'activity_log.causer_type',
            'activity_log.causer_id',
            'activity_log.event',
            'activity_log.properties',
            'activity_log.created_at',
            'users.name as user_name',
            'users.email as user_email'
        )
        ->latest('activity_log.created_at')
        ->limit(200)
        ->get()
        ->map(function ($activity) {
            $module = class_basename($activity->subject_type ?: $activity->log_name ?: 'System');
            $properties = $activity->properties ? json_decode($activity->properties, true) : null;

            return [
                'id' => $activity->id,
                'created_at' => $activity->created_at,
                'date_time' => $activity->created_at,
                'user_name' => $activity->user_name ?? 'System',
                'user_email' => $activity->user_email,
                'causer_id' => $activity->causer_id,
                'action' => $activity->event ?: $activity->description,
                'description' => $activity->description,
                'module' => $module,
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'event' => $activity->event,
                'details' => is_array($properties) && ! empty($properties)
                    ? json_encode($properties, JSON_UNESCAPED_SLASHES)
                    : $activity->description,
                'properties' => $properties,
            ];
        });

    return response()->json($activities);
}

public function interviewers()
{
    return response()->json(
        User::role('PESO')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get()
    );
}


public function createPeso(Request $request)
{
    if (! Auth::user()?->hasAnyRole(['Super Admin', 'Admin', 'PESO Admin'])) {
        abort(403, 'Only Admin or CPESO Admin users can create PESO accounts.');
    }

    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6',
    ]);




    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
    ]);




    $user->assignRole('PESO');




    return back()->with('success', 'PESO Official account created successfully.');
}




}
