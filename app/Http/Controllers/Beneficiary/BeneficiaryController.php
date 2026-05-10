<?php




namespace App\Http\Controllers\Beneficiary;


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




    // (keep if you need applications later)
    $applications = [];




    // ✅ Required document fields
    $fields = [
        'birth_certificate',
        'school_record',
        'osy_certificate',
        'income_proof',
        'displacement_certificate',
        'termination_notice'
    ];




    $documents = [];




    // ============================
    // ✅ IF NO BENEFICIARY
    // ============================
    if (!$beneficiary) {




        foreach ($fields as $field) {
            $documents[] = [
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




    // ============================
    // ✅ GET DOCUMENTS SAFELY
    // ============================
    $existingDocs = $beneficiary->documents ?? [];




    // Convert JSON string → array
    if (is_string($existingDocs)) {
        $existingDocs = json_decode($existingDocs, true) ?: [];
    }




    if (!is_array($existingDocs)) {
        $existingDocs = [];
    }




    // ============================
    // ✅ BUILD DOCUMENT LIST
    // ============================
    foreach ($fields as $field) {




        $status = 'pending';
        $path = null;




        if (isset($existingDocs[$field])) {




            // If stored as object with status
            if (is_array($existingDocs[$field])) {
                $status = $existingDocs[$field]['status'] ?? 'uploaded';
                $path = $existingDocs[$field]['path'] ?? null;
            }
            // If stored as string path only
            else {
                $status = 'uploaded';
                $path = $existingDocs[$field];
            }
        }




        $documents[] = [
            'name' => ucwords(str_replace('_', ' ', $field)),
            'status' => $status,
            'path' => $path,
        ];
    }




    // ============================
    // ✅ FINAL RESPONSE
    // ============================
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

    $applicationIds = Application::where('beneficiary_id', $beneficiary->id)
        ->pluck('id');

    // latest interview
    $interview = Interview::whereIn('application_id', $applicationIds)
        ->latest()
        ->first();

    // latest exam
    $exam = Exam::whereIn('application_id', $applicationIds)
        ->latest()
        ->first();

    // =========================
    // COMPLETED
    // =========================
    if ($beneficiary->is_completed) {
        return response()->json(['status' => 'completed']);
    }

    // =========================
    // APPROVED
    // =========================
    if ($beneficiary->is_approved) {
        return response()->json(['status' => 'approved']);
    }

    // =========================
    // INTERVIEW LOGIC
    // =========================
    if ($interview) {

        // interview passed
        if ($interview->result === 'passed') {
            return response()->json(['status' => 'approved']);
        }

        // interview failed
        if ($interview->result === 'failed') {
            return response()->json(['status' => 'rejected']);
        }

        // interview pending
        return response()->json(['status' => 'interview']);
    }

    // =========================
    // EXAM LOGIC
    // =========================
    if ($exam) {

        // exam passed → proceed to interview
        if ($exam->result === 'passed') {
            return response()->json(['status' => 'interview']);
        }

        // exam failed
        if ($exam->result === 'failed') {
            return response()->json(['status' => 'rejected']);
        }

        // exam pending
        return response()->json(['status' => 'exam']);
    }

    // =========================
    // QUALIFIED
    // =========================
    if ($beneficiary->approval_status === 'approved') {
        return response()->json(['status' => 'qualified']);
    }

    // =========================
    // SCREENING
    // =========================
    if (!empty($beneficiary->documents)) {
        return response()->json(['status' => 'screening']);
    }

    // =========================
    // DEFAULT
    // =========================
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
        if (is_string($existing)) $existing = json_decode($existing, true) ?: [];




        $beneficiary->update([
            'documents' => array_merge((array)$existing, (array)$stored),
            'approval_status' => 'pending',
        ]);




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
        ->map(function ($app) {

            $job = $app->jobListing;

            return [
                'id' => $app->id,
                'job_title' => $job?->title ?? 'No job assigned',
                'employer' => $job?->employer?->company_name ?? 'No employer',
                'is_assigned' => $job ? $job->assigned_beneficiary_id == $app->beneficiary_id : false,
                'status' => $app->status,
                'certificate_path' => $app->certificate_path,
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




    // -------------------------
    // INTERVIEWS API
    // -------------------------
    public function interviewsApi()
    {
        $beneficiary = auth()->user()->beneficiary;
        if (!$beneficiary) return response()->json([]);




        $interviews = Interview::where('beneficiary_id', $beneficiary->id)
            ->where('scheduled_at', '>=', now()->subMinutes(10))
            ->with(['jobListing.employer:id,company_name'])
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                 'type' => 'interview',
                'job_title' => $i->jobListing->title ?? 'Job',
                'employer' => $i->jobListing->employer->company_name ?? 'Employer',
                'scheduled_at' => $i->scheduled_at,
                'meet_link' => $i->meet_link,
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
            ->where('status', 'scheduled')
            ->whereDate('contract_date', '>=', Carbon::today())
            ->orderBy('contract_date', 'asc')
            ->take(5)
            ->get()
            ->map(fn($contract) => [
                'id' => $contract->id,
                'contract_date' => $contract->contract_date,
                'location' => $contract->location ?? 'TBA',
                'status' => $contract->status ?? 'scheduled',
                'result' => $contract->result ?? 'pending',
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
            ->orderBy('contract_date', 'desc')
            ->get()
            ->map(fn($contract) => [
                'id' => $contract->id,
                'contract_date' => $contract->contract_date,
                'location' => $contract->location ?? 'TBA',
                'status' => $contract->status ?? 'scheduled',
                'result' => $contract->result ?? 'pending',
                'notes' => $contract->notes ?? null,
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
            ->with(['jobListing.employer:id,company_name'])
            ->orderBy('scheduled_at', 'desc')
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                'job_title' => $i->jobListing->title ?? 'Job',
                'employer' => $i->jobListing->employer->company_name ?? 'Employer',
                'scheduled_at' => $i->scheduled_at,
                'meet_link' => $i->meet_link,
                'result' => $i->result ?? 'pending',
                'notes' => $i->notes ?? null,
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
        ->map(fn($r) => [
            'id' => $r->id,
            'date' => $r->date,
            'time_in' => $r->time_in,
            'time_out' => $r->time_out,
            'status' => $r->status,
            'notes' => $r->notes, // 🔥 ADD THIS
            'hours' => $r->time_out && $r->time_in
                ? (strtotime($r->time_out) - strtotime($r->time_in)) / 3600
                : 0,
        ]);


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
        $examActivities = Exam::where('beneficiary_id', $beneficiary->id)
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
        $examActivities = Exam::where('beneficiary_id', $beneficiary->id)
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




    $beneficiary->is_approved = false;
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


    $beneficiary = auth()->user()->beneficiary;


    // ❌ If no beneficiary record
    if (!$beneficiary) {
        return response()->json(['message' => 'Beneficiary not found'], 404);
    }


    // ✅ FIX #1: Check if assigned to employer
    if (!$beneficiary->employer_id) {
        return response()->json([
            'message' => 'You are not assigned to any employer'
        ], 403);
    }


    // ✅ Handle file upload
    $proofPath = null;
    if ($request->hasFile('proof')) {
        $proofPath = $request->file('proof')->store('dtr_proofs', 'public');
    }


    // ✅ Save attendance
    $application = Application::where('beneficiary_id', $beneficiary->id)
    ->whereHas('jobListing', function ($q) use ($beneficiary) {
        $q->where('employer_id', $beneficiary->employer_id);
    })
    ->latest()
    ->first();


if (!$application) {
    return response()->json([
        'message' => 'No valid employer assignment'
    ], 403);
}
    $attendance = Attendance::create([
        'beneficiary_id' => $beneficiary->id,
        'employer_id' => $beneficiary->employer_id,
        'date' => $request->date,
        'time_in' => $request->time_in,
        'time_out' => $request->time_out,
        'status' => 'present',
        'remarks' => null,
        'notes' => $proofPath,
    ]);


    return response()->json([
        'message' => 'DTR saved successfully',
        'data' => $attendance
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


    $employer = auth()->user()->employer; // 👈 FIX HERE
dd($request->application_id);
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


}





