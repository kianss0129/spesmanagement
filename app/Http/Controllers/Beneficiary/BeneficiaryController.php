<?php




namespace App\Http\Controllers\Beneficiary;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Models\Beneficiary;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Contract;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\EmployerRating;
use App\Models\WorkHistory;
use App\Models\Exam;




class BeneficiaryController extends Controller
{




public function profile(Request $request)
{
    try {
        // Get the authenticated beneficiary
        $beneficiary = $request->user(); // or Auth::user() depending on your setup


        if (!$beneficiary) {
            return response()->json([
                'error' => 'Beneficiary not found.'
            ], 404);
        }


        // Safely load relationships (null-safe with default empty arrays)
        $profileData = [
            'id' => $beneficiary->id,
            'name' => $beneficiary->name ?? '',
            'email' => $beneficiary->email ?? '',
            'phone' => $beneficiary->phone ?? '',
            'work_schedules' => $beneficiary->workSchedules?->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'start_time' => $schedule->start_time ?? null,
                    'end_time' => $schedule->end_time ?? null,
                ];
            }) ?? [],
            'evaluations' => $beneficiary->evaluations?->map(function ($eval) {
                return [
                    'id' => $eval->id,
                    'score' => $eval->score ?? null,
                    'comments' => $eval->comments ?? '',
                ];
            }) ?? [],
            'work_history' => $beneficiary->workHistory?->map(function ($work) {
                return [
                    'id' => $work->id,
                    'company' => $work->company ?? '',
                    'role' => $work->role ?? '',
                    'start_date' => $work->start_date ?? null,
                    'end_date' => $work->end_date ?? null,
                ];
            }) ?? [],
        ];


        return response()->json([
            'success' => true,
            'data' => $profileData
        ], 200);


    } catch (\Throwable $e) {
        // Log the error for debugging
        \Log::error('Profile API Error: ' . $e->getMessage());


        return response()->json([
            'success' => false,
            'error' => 'An unexpected error occurred.',
            'details' => $e->getMessage(), // optional, remove in production
        ], 500);
    }
}




    public function notifications()
{
        // only show announcements intended for everyone or for beneficiaries
        $announcements = Announcement::forRole('beneficiary')
            ->latest()
            ->get();




        return Inertia::render('Beneficiary/Notifications', [
            'announcements' => $announcements
        ]);
    }

    public function notificationsApi()
    {
        $user = auth()->user();
        $announcements = Announcement::forRole('beneficiary')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($announcement) use ($user) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'image' => $announcement->image,
                    'created_at' => $announcement->created_at,
                    'read' => $announcement->isReadBy($user),
                ];
            });

        return response()->json($announcements);
    }

    public function markNotificationAsRead($id)
    {
        $user = auth()->user();
        $announcement = Announcement::find($id);

        if ($announcement) {
            $announcement->markAsReadBy($user);
            return response()->json(['message' => 'Notification marked as read']);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }

    public function markAllNotificationsAsRead()
    {
        $user = auth()->user();
        $announcements = Announcement::forRole('beneficiary')->get();

        foreach ($announcements as $announcement) {
            $announcement->markAsReadBy($user);
        }

        return response()->json(['message' => 'All notifications marked as read']);
    }

    // -------------------------
    // DASHBOARD
    // -------------------------
    public function dashboard()
    {
        $user = auth()->user();
        $beneficiary = $user->beneficiary;




        return Inertia::render('Beneficiary/Dashboard', [
            'user' => $user,
            'beneficiary' => $beneficiary,
        ]);
    }




    public function dashboardStats()
{
    // ✅ Get authenticated beneficiary
    $beneficiary = auth()->user()->beneficiary ?? null;

    $applications = [];

    // All possible document keys
    $fields = [
        'valid_id',
        'school_enrollment',
        'barangay_certificate',
        'birth_certificate',
        'school_record',
        'osy_certificate',
        'income_proof',
        'displacement_certificate',
        'displacement_proof',
        'termination_notice',
        'parent_valid_id',
    ];

    $documents = [];

    if (!$beneficiary) {
        foreach ($fields as $field) {
            $documents[] = [
                'key' => $field,
                'name' => ucwords(str_replace('_', ' ', $field)),
                'status' => 'pending',
                'path' => null,
            ];
        }

        return response()->json([
            'applications' => $applications,
            'documents' => $documents,
        ]);
    }

    // Get documents safely
    $existingDocs = $beneficiary->documents ?? [];
    if (is_string($existingDocs)) {
        $existingDocs = json_decode($existingDocs, true) ?: [];
    }
    if (!is_array($existingDocs)) {
        $existingDocs = [];
    }

    // Build lookup from both keyed entries and array items with 'type' field
    $docLookup = [];
    foreach ($fields as $field) {
        if (isset($existingDocs[$field])) {
            $docLookup[$field] = $existingDocs[$field];
        }
    }
    foreach ($existingDocs as $entry) {
        if (is_array($entry) && isset($entry['type']) && in_array($entry['type'], $fields, true)) {
            $docLookup[$entry['type']] = $entry;
        }
    }

    // Build document list
    foreach ($fields as $field) {
        $document = $docLookup[$field] ?? null;
        $status = 'pending';
        $path = null;

        if (is_array($document)) {
            $status = $document['status'] ?? ($document['path'] ? 'uploaded' : 'pending');
            $path = $document['path'] ?? null;
        } elseif ($document) {
            $status = 'uploaded';
            $path = $document;
        }

        $documents[] = [
            'key' => $field,
            'name' => ucwords(str_replace('_', ' ', $field)),
            'status' => $status,
            'path' => $path,
        ];
    }

    return response()->json([
        'applications' => $applications,
        'documents' => $documents,
    ]);
}




// -------------------------
// APPLICATION STATUS (Dashboard Progress)
// -------------------------
public function applicationStatus()
{
    $beneficiary = auth()->user()->beneficiary;

    if (!$beneficiary) {
        return response()->json(['status' => 'applied']);
    }

    $latestApplication = Application::where('beneficiary_id', $beneficiary->id)
        ->latest()
        ->first();

    $applicationStatus = strtolower($latestApplication?->status ?? '');

    // Return application.status directly if it's a known workflow value
    $workflowStatuses = [
        'applied',
        'screening',
        'needs_correction',
        'for_exam',
        'exam_passed',
        'for_interview',
        'interview_passed',
        'qualified',
        'approved',
        'assigned',
        'for_contract',
        'contract_signed',
        'deployed',
        'ongoing',
        'completion_review',
        'completed',
        'rejected',
    ];

    if (in_array($applicationStatus, $workflowStatuses, true)) {
        return response()->json(['status' => $applicationStatus]);
    }

    // No application record exists yet
    return response()->json(['status' => 'applied']);
}




    // -------------------------
    // UPLOAD DOCUMENTS
    // -------------------------
    public function uploadDocs(Request $request)
    {
        $beneficiary = auth()->user()->beneficiary;
        if (!$beneficiary) return response()->json(['message' => 'Beneficiary profile not found'], 404);




        $request->validate([
            'documents' => 'sometimes|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
            'birth_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'school_record' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'osy_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'income_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'displacement_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'termination_notice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);




        $folder = "documents/beneficiaries/{$beneficiary->id}";
        $stored = [];




        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $stored[] = $file->store($folder, 'public');
            }
        }




        $fields = ['birth_certificate','school_record','osy_certificate','income_proof','displacement_certificate','termination_notice'];
        foreach ($fields as $field) {
            if ($request->hasFile($field)) $stored[$field] = $request->file($field)->store($folder, 'public');
        }




        if (empty($stored)) return response()->json(['message' => 'No documents uploaded'], 400);



$existing = $beneficiary->documents ?? [];

if (is_string($existing)) {
    $existing = json_decode($existing, true) ?: [];
}

foreach ($stored as $key => $value) {

    if (
        isset($existing[$key]) &&
        Storage::disk('public')->exists($existing[$key])
    ) {
        Storage::disk('public')->delete($existing[$key]);
    }

    $existing[$key] = $value;
}

$beneficiary->update([
    'documents' => $existing,
    'approval_status' => 'pending',
]);

try {
    activity()
        ->causedBy(auth()->user())
        ->performedOn($beneficiary)
        ->withProperties([
            'module' => 'Requirements',
            'user_id' => auth()->id(),
            'status' => 'uploaded',
        ])
        ->log('Beneficiary uploaded requirement');
} catch (\Throwable $e) {
    report($e);
}




        return response()->json(['message' => 'Documents uploaded successfully', 'files' => $stored]);
    }




    // -------------------------
    // APPLICATIONS
    // -------------------------
public function listApplications()
{
    $beneficiary = auth()->user()->beneficiary;


    if (!$beneficiary) {
        return Inertia::render('Beneficiary/Applications', [
            'applications' => []
        ]);
    }


    $applications = Application::where('beneficiary_id', $beneficiary->id)
        ->with(['jobListing.employer'])
        ->latest()
        ->get()
        ->map(function ($app) use ($beneficiary) {

            $job = $app->jobListing;
            $status = $app->status;

            if ($beneficiary->completed_at || $app->status === 'completed') {
                $status = 'completed';
            }

            return [
                'id' => $app->id,
                'job_title' => $job?->title ?? 'No job assigned',
                'employer' => $job?->employer?->company_name ?? 'No employer',
                'is_assigned' => $job ? $job->assigned_beneficiary_id == $app->beneficiary_id : false,
                'status' => $status,
                'certificate_path' => $app->certificate_path,
                'submitted_at' => optional($app->created_at)->toISOString(),
                'updated_at' => optional($app->updated_at)->toISOString(),
                'remarks' => $app->remarks
                    ?? $app->notes
                    ?? $app->rejection_reason
                    ?? $beneficiary->rejection_reason
                    ?? null,
            ];
        });


    return Inertia::render('Beneficiary/Applications', [
        'applications' => $applications
    ]);


}




    // -------------------------
    // INTERVIEWS PAGE
    // -------------------------
    public function interviewsPage()
    {
        return Inertia::render('Beneficiary/Interviews');
    }

    public function certificate()
    {
        $beneficiary = auth()->user()->beneficiary;

        if (!$beneficiary) {
            return Inertia::render('Beneficiary/Certificate', [
                'certificatePath' => null,
                'applicationStatus' => null,
                'completedAt' => null,
            ]);
        }

        $application = Application::where('beneficiary_id', $beneficiary->id)
            ->latest()
            ->first();

        $certificatePath = $application && $application->status === 'completed'
            ? $application->certificate_path
            : null;

        return Inertia::render('Beneficiary/Certificate', [
            'certificatePath' => $certificatePath,
            'applicationStatus' => $application?->status,
            'completedAt' => $beneficiary->completed_at
                ? Carbon::parse($beneficiary->completed_at)->toISOString()
                : null,
        ]);
    }




    // -------------------------
    // INTERVIEWS API
    // -------------------------
    public function interviewsApi()
    {
        $beneficiary = auth()->user()->beneficiary;
        if (!$beneficiary) return response()->json([]);




        $interviews = Interview::where('beneficiary_id', $beneficiary->id)
            ->where('scheduled_at', '>=', now()->subMinutes(10))
            ->with(['jobListing.employer:id,company_name', 'scheduledBy:id,name,email', 'interviewer:id,name,email'])
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                 'type' => 'interview',
                'job_title' => $i->jobListing->title ?? 'Job',
                'employer' => $i->jobListing->employer->company_name ?? 'Employer',
                'scheduled_at' => $i->scheduled_at,
                'end_at' => $i->end_at,
                'meet_link' => $i->meet_link,
                'schedule_group_id' => $i->schedule_group_id,
                'batch_title' => $i->batch_title,
                'scheduled_by' => $i->scheduled_by,
                'scheduled_by_user' => $i->scheduledBy,
                'interviewer_id' => $i->interviewer_id,
                'interviewer' => $i->interviewer,
                'instructions' => $i->instructions,
                'original_schedule_at' => $i->original_schedule_at,
                'rescheduled_at' => $i->rescheduled_at,
                'reschedule_reason' => $i->reschedule_reason,
                'notify_beneficiaries' => $i->notify_beneficiaries,
                'status' => $i->status,
                'result' => $i->result ?? 'pending',
                'can_join' => Carbon::now()->between(
    Carbon::parse($i->scheduled_at)->subMinutes(10),
    Carbon::parse($i->scheduled_at)->addHour() // assuming 1 hour duration
)
            ]);




        return response()->json($interviews);
    }

    // -------------------------
    // CONTRACTS API
    // -------------------------
    public function contractsApi()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $beneficiary = $user->beneficiary;
        if (!$beneficiary) {
            return response()->json(['error' => 'No beneficiary profile found for user'], 401);
        }

        $contracts = Contract::whereHas('application', function ($query) use ($beneficiary) {
                $query->where('beneficiary_id', $beneficiary->id);
            })
            ->with('scheduledBy:id,name,email')
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->whereDate('contract_date', '>=', Carbon::today())
            ->orderBy('contract_date', 'asc')
            ->take(5)
            ->get()
            ->map(fn($contract) => [
                'id' => $contract->id,
                'contract_date' => $contract->contract_date,
                'end_at' => $contract->end_at,
                'location' => $contract->location ?? 'TBA',
                'status' => $contract->status ?? 'scheduled',
                'result' => $contract->result ?? 'pending',
                'schedule_group_id' => $contract->schedule_group_id,
                'batch_title' => $contract->batch_title,
                'scheduled_by' => $contract->scheduled_by,
                'scheduled_by_user' => $contract->scheduledBy,
                'interviewer' => null,
                'instructions' => $contract->instructions,
                'original_schedule_at' => $contract->original_schedule_at,
                'rescheduled_at' => $contract->rescheduled_at,
                'reschedule_reason' => $contract->reschedule_reason,
                'notify_beneficiaries' => $contract->notify_beneficiaries,
            ]);

        return response()->json($contracts);
    }

    // -------------------------
    // CONTRACT HISTORY API
    // -------------------------
    public function contractHistoryApi()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $beneficiary = $user->beneficiary;
        if (!$beneficiary) {
            return response()->json(['error' => 'No beneficiary profile found for user'], 401);
        }

        $contractHistory = Contract::whereHas('application', function ($query) use ($beneficiary) {
                $query->where('beneficiary_id', $beneficiary->id);
            })
            ->with('scheduledBy:id,name,email')
            ->orderBy('contract_date', 'desc')
            ->get()
            ->map(fn($contract) => [
                'id' => $contract->id,
                'contract_date' => $contract->contract_date,
                'end_at' => $contract->end_at,
                'location' => $contract->location ?? 'TBA',
                'status' => $contract->status ?? 'scheduled',
                'result' => $contract->result ?? 'pending',
                'notes' => $contract->notes ?? null,
                'schedule_group_id' => $contract->schedule_group_id,
                'batch_title' => $contract->batch_title,
                'scheduled_by' => $contract->scheduled_by,
                'scheduled_by_user' => $contract->scheduledBy,
                'interviewer' => null,
                'instructions' => $contract->instructions,
                'original_schedule_at' => $contract->original_schedule_at,
                'rescheduled_at' => $contract->rescheduled_at,
                'reschedule_reason' => $contract->reschedule_reason,
                'notify_beneficiaries' => $contract->notify_beneficiaries,
                'created_at' => $contract->created_at,
                'updated_at' => $contract->updated_at,
            ]);

        return response()->json($contractHistory);
    }

    // -------------------------
    // INTERVIEW HISTORY API
    // -------------------------
    public function interviewHistoryApi()
    {
        $beneficiary = auth()->user()->beneficiary;
        if (!$beneficiary) return response()->json([]);

        $interviewHistory = Interview::where('beneficiary_id', $beneficiary->id)
            ->with(['jobListing.employer:id,company_name', 'scheduledBy:id,name,email', 'interviewer:id,name,email'])
            ->orderBy('scheduled_at', 'desc')
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                'job_title' => $i->jobListing->title ?? 'Job',
                'employer' => $i->jobListing->employer->company_name ?? 'Employer',
                'scheduled_at' => $i->scheduled_at,
                'end_at' => $i->end_at,
                'meet_link' => $i->meet_link,
                'result' => $i->result ?? 'pending',
                'notes' => $i->notes ?? null,
                'schedule_group_id' => $i->schedule_group_id,
                'batch_title' => $i->batch_title,
                'scheduled_by' => $i->scheduled_by,
                'scheduled_by_user' => $i->scheduledBy,
                'interviewer_id' => $i->interviewer_id,
                'interviewer' => $i->interviewer,
                'instructions' => $i->instructions,
                'original_schedule_at' => $i->original_schedule_at,
                'rescheduled_at' => $i->rescheduled_at,
                'reschedule_reason' => $i->reschedule_reason,
                'notify_beneficiaries' => $i->notify_beneficiaries,
                'created_at' => $i->created_at,
                'updated_at' => $i->updated_at,
            ]);

        return response()->json($interviewHistory);
    }





    // -------------------------
    // ATTENDANCE OVERVIEW
    // -------------------------
 public function attendance()
{
    $beneficiary = auth()->user()->beneficiary;
    if (!$beneficiary) return response()->json([]);


    $records = Attendance::where('beneficiary_id', $beneficiary->id)
        ->orderBy('date')
        ->get()
        ->map(function ($r) {
            $notes = null;
            $notes_in = null;
            $notes_out = null;
            if ($r->notes) {
                $decoded = json_decode($r->notes, true);
                if (is_array($decoded)) {
                    $notes_in = $decoded['in'] ?? null;
                    $notes_out = $decoded['out'] ?? null;
                } else {
                    // legacy single-path notes
                    $notes_in = $r->notes;
                }
            }

            return [
                'id' => $r->id,
                'date' => $r->date,
                'time_in' => $r->time_in,
                'time_out' => $r->time_out,
                'status' => $r->status,
                'notes_in' => $notes_in,
                'notes_out' => $notes_out,
                'hours' => $r->time_out && $r->time_in
                    ? (strtotime($r->time_out) - strtotime($r->time_in)) / 3600
                    : 0,
            ];
        });


    return response()->json($records);
}

    // -------------------------
    // RECENT ACTIVITIES
    // -------------------------
    public function recentActivities()
    {
        $beneficiary = auth()->user()->beneficiary;
        if (!$beneficiary) return response()->json([]);


        $activities = [];


        // DTR submissions
        $dtrActivities = Attendance::where('beneficiary_id', $beneficiary->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'type' => 'dtr',
                'title' => 'DTR Submitted',
                'description' => 'Submitted attendance for ' . $a->date,
                'date' => $a->created_at,
                'icon' => '📅'
            ]);


        // Ratings received
        $ratingActivities = EmployerRating::where('beneficiary_id', $beneficiary->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($r) => [
                'type' => 'rating',
                'title' => 'Rating Received',
                'description' => 'Received performance rating from employer',
                'date' => $r->created_at,
                'icon' => '⭐'
            ]);


        // Application status changes
        $applicationActivities = Application::where('beneficiary_id', $beneficiary->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($app) => [
                'type' => 'application',
                'title' => 'Application Update',
                'description' => 'Application status: ' . ucfirst($app->status),
                'date' => $app->updated_at,
                'icon' => '📋'
            ]);


        // Interview updates
        $interviewActivities = Interview::where('beneficiary_id', $beneficiary->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($i) => [
                'type' => 'interview',
                'title' => 'Interview ' . ucfirst($i->result ?? 'scheduled'),
                'description' => 'Interview result: ' . ($i->result ?? 'Pending'),
                'date' => $i->updated_at,
                'icon' => '🎤'
            ]);


        // Exam updates
        $examActivities = Exam::whereHas('application', function ($query) use ($beneficiary) {
                $query->where('beneficiary_id', $beneficiary->id);
            })
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($e) => [
                'type' => 'exam',
                'title' => 'Exam ' . ucfirst($e->result ?? 'scheduled'),
                'description' => 'Exam result: ' . ($e->result ?? 'Pending'),
                'date' => $e->updated_at,
                'icon' => '📝'
            ]);


        // Combine and sort by date
        $activities = collect([
            ...$dtrActivities,
            ...$ratingActivities,
            ...$applicationActivities,
            ...$interviewActivities,
            ...$examActivities
        ])->sortByDesc('date')->take(10)->values();


        return response()->json($activities);
    }


    // -------------------------
    // ALL ACTIVITIES
    // -------------------------
    public function allActivities()
    {
        $beneficiary = auth()->user()->beneficiary;
        if (!$beneficiary) return response()->json([]);


        $activities = [];


        // DTR submissions
        $dtrActivities = Attendance::where('beneficiary_id', $beneficiary->id)
            ->latest()
            ->get()
            ->map(fn($a) => [
                'type' => 'dtr',
                'title' => 'DTR Submitted',
                'description' => 'Submitted attendance for ' . $a->date,
                'date' => $a->created_at,
                'icon' => '📅'
            ]);


        // Ratings received
        $ratingActivities = EmployerRating::where('beneficiary_id', $beneficiary->id)
            ->latest()
            ->get()
            ->map(fn($r) => [
                'type' => 'rating',
                'title' => 'Rating Received',
                'description' => 'Received performance rating from employer',
                'date' => $r->created_at,
                'icon' => '⭐'
            ]);


        // Application status changes
        $applicationActivities = Application::where('beneficiary_id', $beneficiary->id)
            ->latest()
            ->get()
            ->map(fn($app) => [
                'type' => 'application',
                'title' => 'Application Update',
                'description' => 'Application status: ' . ucfirst($app->status),
                'date' => $app->updated_at,
                'icon' => '📋'
            ]);


        // Interview updates
        $interviewActivities = Interview::where('beneficiary_id', $beneficiary->id)
            ->latest()
            ->get()
            ->map(fn($i) => [
                'type' => 'interview',
                'title' => 'Interview ' . ucfirst($i->result ?? 'scheduled'),
                'description' => 'Interview result: ' . ($i->result ?? 'Pending'),
                'date' => $i->updated_at,
                'icon' => '🎤'
            ]);


        // Exam updates
        $examActivities = Exam::whereHas('application', function ($query) use ($beneficiary) {
                $query->where('beneficiary_id', $beneficiary->id);
            })
            ->latest()
            ->get()
            ->map(fn($e) => [
                'type' => 'exam',
                'title' => 'Exam ' . ucfirst($e->result ?? 'scheduled'),
                'description' => 'Exam result: ' . ($e->result ?? 'Pending'),
                'date' => $e->updated_at,
                'icon' => '📝'
            ]);


        // Combine and sort by date
        $activities = collect([
            ...$dtrActivities,
            ...$ratingActivities,
            ...$applicationActivities,
            ...$interviewActivities,
            ...$examActivities
        ])->sortByDesc('date')->values();


        return response()->json($activities);
    }


    // -------------------------
    // WORK HISTORY
    // -------------------------
    public function workHistory($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);




        return response()->json([
            'beneficiary' => $beneficiary->first_name . ' ' . $beneficiary->last_name,
            'timeline' => $beneficiary->workHistory()->get()
        ]);
    }




    public function myWorkHistory()
    {
        $beneficiary = auth()->user()->beneficiary;
        if (!$beneficiary) return response()->json(['timeline' => []]);




        return $this->workHistory($beneficiary->id);
    }




    // -------------------------
    // PROFILE PAGE
    // -------------------------
    public function profilePage()
    {
        $beneficiary = auth()->user()->beneficiary;
        $EmployerRatings = $beneficiary?->EmployerRatings ?? collect();
        $average = round($EmployerRatings->avg('overall') ?? 0, 1);




        return Inertia::render('Beneficiary/Profile', [
            'beneficiary' => $beneficiary,
            'EmployerRatings' => $EmployerRatings,
            'average' => $average,
        ]);
    }




    // -------------------------
    // JOBS PAGE
    // -------------------------
  public function jobs()
    {
        return \App\Models\JobListing::with('employer')
            ->withCount(['applications as assigned_count'])
            ->latest()
            ->get();
    }



    public function revert($id)
{
    $beneficiary = \App\Models\Beneficiary::findOrFail($id);




    $beneficiary->approved = false;
    $beneficiary->approval_status = 'pending';
    $beneficiary->status = 'Pending';
    $beneficiary->save();




    return response()->json([
        'message' => 'Beneficiary reverted to Pending successfully.'
    ]);
}
public function storeDTR(Request $request)
{
    $request->validate([
        'date' => 'required|date',
        'time_in' => 'required',
        'time_out' => 'nullable',
        'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ]);

    // Override client-submitted date/time with internet-synced Philippine time
    // This prevents DTR manipulation via device clock changes
    $phTime = $this->getPhilippineTime();
    $serverDate = $phTime->format('Y-m-d');
    $serverTimeNow = $phTime->format('H:i');

    // Use server-verified Philippine time instead of client values
    $request->merge([
        'date' => $serverDate,
        'time_in' => $request->time_out ? $request->time_in : $serverTimeNow,
        'time_out' => $request->time_out ? $serverTimeNow : null,
    ]);

    $beneficiary = auth()->user()->beneficiary;

    if (!$beneficiary) {
        return response()->json(['message' => 'Beneficiary not found'], 404);
    }

    $employerId = $beneficiary->employer_id;
    $application = null;

    if ($beneficiary->job_id) {
        $application = Application::where('beneficiary_id', $beneficiary->id)
            ->where('job_listing_id', $beneficiary->job_id)
            ->with('jobListing')
            ->latest()
            ->first();
    }

    if (!$application) {
        $application = Application::where('beneficiary_id', $beneficiary->id)
            ->with('jobListing')
            ->where(function ($query) use ($beneficiary) {
                $query->where('status', 'assigned')
                    ->orWhere('status', 'ongoing')
                    ->orWhereHas('jobListing', function ($q) use ($beneficiary) {
                        $q->where('assigned_beneficiary_id', $beneficiary->id);
                    });
            })
            ->latest()
            ->first();
    }

    if (!$employerId && $application?->jobListing?->employer_id) {
        $employerId = $application->jobListing->employer_id;
    }

    if (!$employerId) {
        return response()->json([
            'message' => 'You are not assigned to any employer'
        ], 403);
    }

    $proofPath = null;
    if ($request->hasFile('proof')) {
        $proofPath = $request->file('proof')->store('dtr_proofs', 'public');
    }

    $openAttendance = Attendance::where('beneficiary_id', $beneficiary->id)
        ->where('date', $request->date)
        ->whereNotNull('time_in')
        ->whereNull('time_out')
        ->latest()
        ->first();

    $completedAttendance = Attendance::where('beneficiary_id', $beneficiary->id)
        ->where('date', $request->date)
        ->whereNotNull('time_in')
        ->whereNotNull('time_out')
        ->first();

    if (!$openAttendance && $completedAttendance) {
        return response()->json([
            'message' => 'Attendance for this date has already been completed. You cannot submit another Time In/Out for the same day.'
        ], 422);
    }

    if ($openAttendance && $request->time_out) {
        // require proof for time out
        if (!$proofPath) {
            return response()->json(['message' => 'Proof photo is required for Time Out'], 422);
        }

        $openAttendance->time_out = $request->time_out;
        $openAttendance->status = 'present';

        // preserve existing notes structure and add 'out'
        $existing = [];
        if ($openAttendance->notes) {
            $decoded = json_decode($openAttendance->notes, true);
            if (is_array($decoded)) $existing = $decoded;
            else $existing = ['in' => $openAttendance->notes];
        }

        $existing['out'] = $proofPath;
        $openAttendance->notes = json_encode($existing);

        $openAttendance->save();

        $responseData = [
            'id' => $openAttendance->id,
            'date' => $openAttendance->date,
            'time_in' => $openAttendance->time_in,
            'time_out' => $openAttendance->time_out,
            'status' => $openAttendance->status,
            'notes_in' => $existing['in'] ?? null,
            'notes_out' => $existing['out'] ?? null,
        ];

        try {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($openAttendance)
                ->withProperties([
                    'module' => 'Attendance',
                    'user_id' => auth()->id(),
                    'status' => 'timed_out',
                ])
                ->log('Beneficiary timed out attendance');
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'DTR updated successfully',
            'data' => $responseData
        ]);
    }

    if ($openAttendance && !$request->time_out) {
        return response()->json([
            'message' => 'Please submit Time Out for your open attendance record.'
        ], 422);
    }

    if (!$application) {
        $application = Application::where('beneficiary_id', $beneficiary->id)
            ->whereHas('jobListing', function ($q) use ($employerId) {
                $q->where('employer_id', $employerId);
            })
            ->latest()
            ->first();
    }

    if (!$application) {
        return response()->json([
            'message' => 'No valid employer assignment'
        ], 403);
    }

    // require proof for time-in
    if (!$proofPath) {
        return response()->json(['message' => 'Proof photo is required for Time In'], 422);
    }

    $notesPayload = json_encode(['in' => $proofPath, 'out' => null]);

    $attendance = Attendance::create([
        'beneficiary_id' => $beneficiary->id,
        'employer_id' => $employerId,
        'application_id' => $application->id,
        'date' => $request->date,
        'time_in' => $request->time_in,
        'time_out' => $request->time_out,
        'status' => 'present',
        'remarks' => null,
        'notes' => $notesPayload,
    ]);

    // Auto-transition: deployed → ongoing on first DTR submission
    if ($application->status === 'deployed') {
        $application->update(['status' => 'ongoing']);
    }

    $responseData = [
        'id' => $attendance->id,
        'date' => $attendance->date,
        'time_in' => $attendance->time_in,
        'time_out' => $attendance->time_out,
        'status' => $attendance->status,
        'notes_in' => $proofPath,
        'notes_out' => null,
    ];

    try {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($attendance)
            ->withProperties([
                'module' => 'Attendance',
                'user_id' => auth()->id(),
                'status' => 'submitted',
            ])
            ->log('Beneficiary submitted attendance');
    } catch (\Throwable $e) {
        report($e);
    }

    return response()->json([
        'message' => 'DTR saved successfully',
        'data' => $responseData
    ]);
}


public function pendingEmployerRatings()
{
    $user = Auth::user();


    $beneficiaries = \App\Models\Beneficiary::where('employer_id', $user->id)
        ->get()
        ->map(function ($b) {
            return [
                'id' => $b->id,
                'name' => $b->name,
            ];
        });


    return Inertia::render('Beneficiary/PendingEmployerRatings', [
        'beneficiaries' => $beneficiaries
    ]);
}


public function EmployerRatingsHistory()
{
    $user = Auth::user();


    $EmployerRatings = EmployerRating::where('beneficiary_id', $user->id)
        ->latest()
        ->get()
        ->map(function ($r) {
            return [
                'id' => $r->id,
                'beneficiary_name' => $r->beneficiary->name ?? 'Unknown',
                'punctuality' => $r->punctuality,
                'work_quality' => $r->work_quality,
                'attitude' => $r->attitude,
                'communication' => $r->communication,
                'overall' => $r->overall,
                 'comment' => $r->comment ?? 'No comment',
                'created_at' => $r->created_at,
            ];
        });


    return Inertia::render('Beneficiary/EmployerRatingsHistory', [
        'EmployerRatings' => $EmployerRatings
    ]);
}
public function pendingEmployerRatingsApi()
{
    $user = auth()->user();


    $beneficiaries = Beneficiary::where('employer_id', $user->id)
        ->get()
        ->map(function ($b) {
            return [
                'id' => $b->id,
                'name' => $b->user->name ?? 'Unknown'
            ];
        });


    return response()->json($beneficiaries);
}


public function storeEmployerRating(Request $request)
{
    $request->validate([
        'application_id' => 'required',
        'beneficiary_id' => 'required',
        'punctuality' => 'required',
        'work_quality' => 'required',
        'work_attitude' => 'required',
        'communication' => 'required',
        'overall' => 'required',
    ]);


    $employer = auth()->user()->employer;

    if (! $employer) {
        return response()->json([
            'success' => false,
            'message' => 'Employer profile not found.',
        ], 403);
    }
    EmployerRating::create([
        'employer_id' => $employer->id,
        'application_id' => $request->application_id, // ✅ FIXED
        'beneficiary_id' => $request->beneficiary_id,


        'punctuality' => $request->punctuality,
        'work_quality' => $request->work_quality,
        'attitude' => $request->work_attitude,
        'communication' => $request->communication,
        'overall' => $request->overall,
        'comment' => $request->comment,
    ]);


    return response()->json([
        'success' => true,
        'message' => 'Rating saved'
    ]);
}


public function EmployerRatingsHistoryApi()
{
    $user = auth()->user();


    return EmployerRating::where('employer_id', $user->id)
        ->with('beneficiary.user')
        ->latest()
        ->get()
        ->map(function ($r) {
            return [
                'id' => $r->id,
                'beneficiary_name' => $r->beneficiary->user->name ?? 'Unknown',
                'punctuality' => $r->punctuality,
                'work_quality' => $r->work_quality,
                'attitude' => $r->attitude,
                'communication' => $r->communication,
                'overall' => $r->overall,
                 'comment' => $r->comment ?? 'No comment',
                'created_at' => $r->created_at,
            ];
        });
}


public function historyApi()
{
    $user = auth()->user();


    return WorkHistory::where('beneficiary_id', $user->id)
        ->with('employer')
        ->latest()
        ->get()
        ->map(function ($h) {
            return [
                'id' => $h->id,
                'employer' => $h->employer->company_name ?? 'Unknown',
                'EmployerRating' => $h->EmployerRatingRating ?? 0,
                'comment' => $h->comment ?? '',
            ];
        });
}


public function ratingsHistory()
{
    $user = auth()->user();
    $beneficiary = $user->beneficiary;


    $ratings = \App\Models\EmployerRating::with('employer')
        ->where('beneficiary_id', $beneficiary->id)
        ->latest()
        ->get()
        ->map(function ($r) {
            return [
                'id' => $r->id,
                'employer_name' => optional($r->employer)->company_name ?? 'Unknown',


                'punctuality' => $r->punctuality,
                'work_quality' => $r->work_quality,
                'attitude' => $r->attitude,
                'communication' => $r->communication,
                'overall' => $r->overall,
                'comment' => $r->comment,
                'created_at' => $r->created_at,
            ];
        });


    return Inertia::render('Beneficiary/RatingsHistory', [
        'ratings' => $ratings
    ]);
}




public function pendingRatings()
{
    $user = auth()->user();
    $beneficiary = $user->beneficiary;


    if (!$beneficiary) {
        return Inertia::render('Beneficiary/RatingsHistory', [
            'ratings' => []
        ]);
    }


    $ratings = EmployerRating::where('beneficiary_id', $beneficiary->id)
        ->with('employer')
        ->latest()
        ->get()
        ->map(function ($r) {
            return [
                'id' => $r->id,
                'employer' => $r->employer->company_name ?? 'Unknown',
                'punctuality' => $r->punctuality,
                'work_quality' => $r->work_quality,
                'attitude' => $r->attitude,
                'communication' => $r->communication,
                'overall' => $r->overall,
                'created_at' => $r->created_at,
            ];
        });


    return Inertia::render('Beneficiary/RatingsHistory', [
        'ratings' => $ratings
    ]);
}


public function history()
{
    $user = auth()->user();
    $beneficiary = $user->beneficiary;


    if (!$beneficiary) {
        return Inertia::render('Beneficiary/WorkHistory', [
            'history' => []
        ]);
    }


    $history = \App\Models\WorkHistory::where('beneficiary_id', $beneficiary->id)
        ->with('employer')
        ->latest()
        ->get()
        ->map(function ($h) {
            return [
                'id' => $h->id,
                'employer' => $h->employer->company_name ?? 'Unknown',
                'rating' => $h->rating ?? 0,
                'comment' => $h->comment ?? '',
                'created_at' => $h->created_at,
            ];
        });


    return Inertia::render('Beneficiary/WorkHistory', [
        'history' => $history
    ]);
}

/**
 * Get current Philippine Standard Time from WorldTimeAPI.
 * Falls back to server time in Asia/Manila if API is unavailable.
 * Used exclusively by storeDTR() to prevent client-side time manipulation.
 */
private function getPhilippineTime(): \Carbon\Carbon
{
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(3)
            ->get('http://worldtimeapi.org/api/timezone/Asia/Manila');

        if ($response->successful()) {
            $datetime = $response->json('datetime');
            if ($datetime) {
                return \Carbon\Carbon::parse($datetime);
            }
        }

        \Illuminate\Support\Facades\Log::warning('DTR: WorldTimeAPI returned non-success response.');
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::warning('DTR: WorldTimeAPI unavailable - ' . $e->getMessage());
    }

    // Fallback: server time in Philippine timezone
    return now()->setTimezone('Asia/Manila');
}

}
