<?php


namespace App\Http\Controllers\PESO;


use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Models\JobListing;
use App\Models\Beneficiary;
use App\Models\Employer;
use App\Models\EmployerRating;
use App\Models\EmployerCompliance;
use App\Models\Attendance;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Announcement;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;
use App\Models\User;


class PESOController extends Controller
{
public function index()
{
    // Get all applications
    $applications = \App\Models\Application::with([
        'beneficiary',
        'jobListing.employer'
    ])
    ->get()
    ->map(function ($app) {
        return [
            'id' => $app->id,
            'beneficiary_name' => trim(
                ($app->beneficiary->first_name ?? '') . ' ' . ($app->beneficiary->last_name ?? '')
            ) ?: 'N/A',
            'job_title' => $app->jobListing?->title ?? 'N/A',
            'employer_name' => $app->jobListing?->employer?->company_name ?? 'N/A',
            'status' => $app->status,
            'beneficiary_id' => $app->beneficiary_id,
            'is_real_application' => true,
        ];
    });


    // Get approved beneficiaries who don't have any applications at all
    $allApplicationBeneficiaryIds = \App\Models\Application::pluck('beneficiary_id')->unique();


    $unassignedBeneficiaries = \App\Models\Beneficiary::with('user')
        ->where('approved', true)
        ->whereNotIn('id', $allApplicationBeneficiaryIds)
        ->get()
        ->map(function ($beneficiary) {
            return [
                'id' => 'unassigned_' . $beneficiary->id, // Pseudo ID to avoid conflicts
                'beneficiary_name' => trim(
                    ($beneficiary->first_name ?? '') . ' ' . ($beneficiary->last_name ?? '')
                ) ?: ($beneficiary->user->name ?? 'N/A'),
                'job_title' => null,
                'employer_name' => null,
                'status' => 'unassigned',
                'beneficiary_id' => $beneficiary->id,
                'is_real_application' => false,
            ];
        });


    // Combine and return
    return $applications->concat($unassignedBeneficiaries);
}


public function getReports()
{
    $user = auth()->user();

    $reports = Report::query();

    if ($user && $user->hasRole('Employer')) {
        $employer = \App\Models\Employer::where('user_id', $user->id)->first();
        $reports->where('employer_id', $employer?->id);
    }

    return $reports->get()->map(function ($report) {
        return [
            'id' => $report->id,
            'title' => $report->title,
            'body' => $report->body,
            'employer_id' => $report->employer_id,
            'created_at' => $report->created_at,
            'file_path' => $report->file_path,
            'file_url' => $report->file_path ? Storage::url($report->file_path) : null,
        ];
    });
}
public function employerCompliancePage()
{
    $employers = Employer::select('id', 'company_name')
        ->withCount([
            'beneficiaries as compliance_rate' => function ($q) {
                $q->select(DB::raw('ROUND(AVG(compliance_score))'));
            }
        ])
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
        return EmployerCompliance::with('employer')->get();
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
        return Interview::with('applicant','employer')->get();
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
                    'name' => $beneficiary->user?->name ?? 'N/A',
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
                    'applications_count' => $job->applications->count(),
                    'status' => $job->status,
                ];
            });


        return response()->json($jobListings);
    }


 public function assignBeneficiary(Request $request)
{
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


return response()->json(['message' => 'Beneficiary assigned successfully.']);
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
    $beneficiaries = Beneficiary::with('user')
        ->where('approved', true)
        ->get()
        ->map(fn($b) => [
            'id' => $b->id,
            'name' => $b->user?->name ?? $b->name ?? 'N/A',
            'email' => $b->user?->email ?? 'N/A',
            'approved_at' => $b->approved_at?->format('Y-m-d H:i') ?? 'N/A',
            'approval_status' => $b->approval_status ?? 'approved',
        ]);


    return response()->json($beneficiaries);
}


/**
 * ==========================
 * APPROVED EMPLOYERS
 * ==========================
 */
public function approvedEmployers()
{
    $employers = Employer::with('user')
        ->where('approved', true)
        ->get()
        ->map(fn($e) => [
            'id' => $e->id,
            'company_name' => $e->company_name,
            'email' => $e->user?->email ?? $e->email ?? 'N/A',
            'contact_person' => $e->contact_person ?? 'N/A',
            'approved_at' => $e->approved_at?->format('Y-m-d H:i') ?? 'N/A',
            'approval_status' => $e->approval_status ?? 'approved',
        ]);


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


public function createPeso(Request $request)
{
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




    $user->assignRole('Admin');




    return back()->with('success', 'PESO Official account created successfully.');
}




}

