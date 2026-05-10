<?php
namespace App\Http\Controllers\Employer;

    use App\Http\Controllers\Controller;
    use App\Models\JobListing;
    use App\Models\Application;
    use App\Models\Attendance;
    use App\Models\EmployerRating;
    use App\Models\Beneficiary;
    use App\Models\Interview;
    use App\Services\GoogleCalendarService;
    use App\Services\NotificationService;
    use Illuminate\Http\Request;
    use Inertia\Inertia;
    use Illuminate\Support\Facades\DB;
    use App\Models\Announcement;
    use App\Models\Report;

    class EmployerController extends Controller
    {
    public function updateJobStatus(Request $request, $id)
    {
        $application = Application::findOrFail($id);
        $application->status = $request->status;
        $application->save();

        return response()->json([
            'message' => 'Status updated successfully'
        ]);
    }

    public function uploadCertificate(Request $request, $id)
    {
        $request->validate([
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);

        try {
            $application = Application::findOrFail($id);
            
            if ($request->hasFile('certificate')) {
                $file = $request->file('certificate');
                $path = $file->store('certificates', 'public');
                
                $application->certificate_path = $path;
                $application->save();

                return response()->json([
                    'message' => 'Certificate uploaded successfully',
                    'path' => $path
                ]);
            }

            return response()->json([
                'error' => 'No file provided'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function notifications()
    {
    $announcements = Announcement::where(function ($query) {

        $query->where('target_role', 'all')
                ->orWhere('target_role', 'employer');
    })
    ->latest()
    ->get();

    return Inertia::render('Employer/Notification', [
        'announcements' => $announcements
    ]);
    }

    public function notificationsPage()
    {
    $announcements = Announcement::where(function ($query) {
        $query->where('target_role', 'all')
                ->orWhere('target_role', 'employer');
    })
    ->latest()
    ->get();

    return Inertia::render('Employer/Notification', [
        'announcements' => $announcements
    ]);
    }

    public function notificationsData()
    {
    $announcements = Announcement::where(function ($query) {
        $query->where('target_role', 'all')
            ->orWhere('target_role', 'employer');
    })
    ->latest()
    ->get();

    return response()->json($announcements);
    }


        // Create Job Listing
        public function storeJob(Request $request)
        {
            $data = $request->validate([
                'title'=>'required|string',
                'description'=>'nullable|string',
                'positions'=>'required|integer',
                'start_date'=>'nullable|date',
                'end_date'=>'nullable|date',
            ]);

            $data['employer_id'] = auth()->user()->id;
            $job = JobListing::create($data);

            return response()->json(['message'=>'Job created successfully','job'=>$job]);
        }


        public function settings()
    {
    $sessions = DB::table('sessions')
        ->where('user_id', auth()->id())
        ->orderByDesc('last_activity')
        ->get();
        return Inertia::render('Employer/Settings', [
            'sessions' => $sessions
        ]);
    }

        // View applicants
    public function index()
    {
        $employerId = auth()->user()->employer->id;

    $applications = \App\Models\Application::with([
        'jobListing',
        'beneficiary.user',
        'beneficiary.school'
    ])
    ->whereHas('jobListing', fn($q) => $q->where('employer_id', $employerId))
    ->latest()
    ->get()
    ->map(function($app) {
        // Add profile_photo_url to beneficiary for Vue
        $app->beneficiary->profile_photo_url = $app->beneficiary->user && $app->beneficiary->user->profile_photo_path
            ? asset('storage/' . $app->beneficiary->user->profile_photo_path)
            : '/default-profile.png';
        return $app;
    })
    ->groupBy(fn($app) => $app->jobListing->title);

        return Inertia::render('Employer/Applicants', [
            'applications' => $applications
        ]);
    }

    public function completionRate()
    {
        $employerId = auth()->user()->employer->id;

        $applications = Application::with([
            'jobListing',
            'beneficiary.user',
            'beneficiary.school'
        ])
        ->whereHas('jobListing', fn($q) => $q->where('employer_id', $employerId))
        ->latest()
        ->get()
        ->map(function ($app) {
            $app->beneficiary->profile_photo_url = $app->beneficiary->user && $app->beneficiary->user->profile_photo_path
                ? asset('storage/' . $app->beneficiary->user->profile_photo_path)
                : '/default-profile.png';
            return $app;
        })
        ->groupBy(fn($app) => $app->jobListing->title);

        return Inertia::render('Employer/CompletionRate', [
            'applications' => $applications
        ]);
    }

        // Submit Attendance (batch or single)
        public function submitAttendance(Request $request)
        {
            if ($request->has('records')) {
                $data = $request->validate([
                    'date'=>'required|date',
                    'records'=>'array',
                    'records.*.beneficiary_id'=>'required|exists:beneficiaries,id',
                    'records.*.time_in'=>'nullable',
                    'records.*.time_out'=>'nullable',
                    'records.*.notes'=>'nullable|string'
                ]);
                $created = [];
                foreach ($data['records'] as $rec){
                    $rec['date']=$data['date'];
                    $rec['employer_id']=auth()->id();
                    $created[]=Attendance::create($rec);
                }
                return response()->json(['message'=>'Attendance submitted','attendance'=>$created]);
            }

            $data = $request->validate([
                'beneficiary_id'=>'required|exists:beneficiaries,id',
                'date'=>'required|date',
                'time_in'=>'nullable',
                'time_out'=>'nullable',
                'notes'=>'nullable|string'
            ]);
            $data['employer_id']=auth()->id();
            $attendance=Attendance::create($data);

            return response()->json(['message'=>'Attendance submitted','attendance'=>$attendance]);
        }

        // Submit rating
    public function submitRating(Request $request)
{
    $request->validate([
        'beneficiary_id' => 'required|exists:beneficiaries,id',
        'punctuality' => 'required|integer|min:1|max:5',
        'work_quality' => 'required|integer|min:1|max:5',
        'work_attitude' => 'required|integer|min:1|max:5',
        'communication' => 'required|integer|min:1|max:5',
        'overall' => 'required|integer|min:1|max:5',
         'comment' => 'nullable|string',
    ]);

    $employer = auth()->user()->employer;

    if (!$employer) {
        return response()->json([
            'message' => 'Employer not found'
        ], 400);
    }

    $rating = EmployerRating::create([
        'beneficiary_id' => $request->beneficiary_id,
        'employer_id' => $employer->id,
        'punctuality' => $request->punctuality,
        'work_quality' => $request->work_quality,
        'work_attitude' => $request->work_attitude,
        'communication' => $request->communication,
        'overall' => $request->overall,
        'comment' => $request->comment ?? 'No comment',

    ]);

    return response()->json([
        'message' => 'Rating submitted successfully',
        'rating' => $rating
    ]);
}
    
        // Recommended candidates
        public function recommendedCandidates()
        {
            $recommended = Beneficiary::withAvg('ratings','overall')
                ->withCount(['attendances as attendance_present'=>fn($q)=>$q->whereNotNull('time_in')])
                ->orderByDesc('ratings_avg_overall')
                ->orderByDesc('attendance_present')
                ->limit(10)
                ->get();

            return response()->json($recommended);
        }

        // Applicant ratings
        public function applicantRatings($beneficiaryId)
        {
            return EmployerRating::where('beneficiary_id',$beneficiaryId)->with('employer','application')->get();
        }

        // Choose applicant
        public function chooseApplicant($jobId,$applicationId)
        {
            $job = JobListing::findOrFail($jobId);
            $app = Application::where('job_listing_id',$jobId)->where('id',$applicationId)->firstOrFail();
            $app->status='selected';
            $app->selected_at=now();
            $app->save();

            return response()->json(['message'=>'Applicant selected','application'=>$app]);
        }

    public function interviews()
    {
        $employer = \App\Models\Employer::where('user_id', auth()->id())->first();

        if (!$employer) {
            return response()->json([]);
        }

    $interviews = Interview::where('employer_id', $employer->id)
        ->where('scheduled_at', '>=', now())
        ->with(['application.jobListing', 'application.beneficiary'])
        ->orderByDesc('scheduled_at')
        ->get()
        ->map(function ($i) {

            $beneficiary = $i->application->beneficiary;

            return [
                'id' => $i->id,
                'scheduled_at' => $i->scheduled_at,
                'meet_link' => $i->meet_link,
                'job_title' => optional($i->application->jobListing)->title,
                'beneficiary_name' => $beneficiary
                    ? $beneficiary->first_name . ' ' . $beneficiary->last_name
                    : '',
            ];
        });

        return response()->json($interviews);
    }

        // List attendance
        public function listAttendance()
        {
            return Attendance::where('employer_id',auth()->id())->with('beneficiary')->orderByDesc('date')->limit(200)->get();
        }

        // Submit work outputs
        public function submitWorkOutput(Request $request)
        {
            $request->validate([
                'beneficiary_id'=>'required|integer',
                'files'=>'required|array',
                'files.*'=>'file|max:10240'
            ]);

            $records=[];
            foreach ($request->file('files') as $f){
                $path = $f->store('work_outputs','public');
                $records[]= \App\Models\WorkOutput::create([
                    'employer_id'=>auth()->id(),
                    'beneficiary_id'=>$request->input('beneficiary_id'),
                    'file_path'=>$path,
                    'original_name'=>$f->getClientOriginalName()
                ]);
            }

            return response()->json(['message'=>'Work outputs uploaded','records'=>$records]);
        }

        // Employer dashboard
        public function dashboard()
        {
            return Inertia::render('Employer/Dashboard');
        }

    public function stats(Request $request)
    {
        $employer = auth()->user()->employer;
        $employerId = $employer->id ?? null;

        if (!$employerId) {
            return response()->json([
                'open_jobs' => 0,
                'applicants' => 0,
                'upcoming_interviews' => 0,
                'pending_ratings' => 0,
                'today_attendance' => 0,
                'completion_rate' => 0,
                'completed_applications' => 0,
                'ongoing_applications' => 0,
                'not_started_applications' => 0,
                'rating_summary' => [
                    'punctuality' => 0,
                    'work_quality' => 0,
                    'attitude' => 0,
                    'communication' => 0,
                    'overall' => 0,
                    'count' => 0,
                ],
                'recent_ratings' => [],
            ]);
        }

        $totalApplications = Application::whereHas('jobListing', fn($q) => $q->where('employer_id', $employerId))->count();
        $completedApplications = Application::whereHas('jobListing', fn($q) => $q->where('employer_id', $employerId))
            ->where('status', 'completed')
            ->count();
        $ongoingApplications = Application::whereHas('jobListing', fn($q) => $q->where('employer_id', $employerId))
            ->where('status', 'ongoing')
            ->count();
        $notStartedApplications = max(0, $totalApplications - $completedApplications - $ongoingApplications);

        $ratingQuery = EmployerRating::where('employer_id', $employerId);
        $ratingCount = $ratingQuery->count();

        $ratingSummary = [
            'punctuality' => $ratingCount ? round($ratingQuery->avg('punctuality'), 1) : 0,
            'work_quality' => $ratingCount ? round($ratingQuery->avg('work_quality'), 1) : 0,
            'attitude' => $ratingCount ? round($ratingQuery->avg('attitude'), 1) : 0,
            'communication' => $ratingCount ? round($ratingQuery->avg('communication'), 1) : 0,
            'overall' => $ratingCount ? round($ratingQuery->avg('overall'), 1) : 0,
            'count' => $ratingCount,
        ];

        $recentRatings = $ratingQuery->latest()->limit(6)->get()->map(fn($rating) => [
            'id' => $rating->id,
            'beneficiary_name' => optional($rating->beneficiary)->full_name ?? 'Unknown',
            'punctuality' => $rating->punctuality,
            'work_quality' => $rating->work_quality,
            'attitude' => $rating->attitude,
            'communication' => $rating->communication,
            'overall' => $rating->overall,
            'comment' => $rating->comment,
            'created_at' => $rating->created_at,
        ]);

        return response()->json([
            'open_jobs' => JobListing::where('employer_id', $employerId)->count(),
            'applicants' => $totalApplications,
            'upcoming_interviews' => Interview::whereHas('application.jobListing', fn($q) => $q->where('employer_id', $employerId))
                ->where('scheduled_at', '>=', now())
                ->count(),
            'pending_ratings' => EmployerRating::where('employer_id', $employerId)->count(),
            'today_attendance' => Attendance::where('employer_id', $employerId)
                ->whereDate('date', today())
                ->count(),
            'completion_rate' => $totalApplications ? round($completedApplications / $totalApplications * 100) : 0,
            'completed_applications' => $completedApplications,
            'ongoing_applications' => $ongoingApplications,
            'not_started_applications' => $notStartedApplications,
            'rating_summary' => $ratingSummary,
            'recent_ratings' => $recentRatings,
        ]);
    }

    public function analyticsApplicantsPerJob() {
        $employer = auth()->user()->employer;
        $employerId = $employer->id ?? null;

        if (!$employerId) {
            return response()->json([]);
        }

        $jobs = JobListing::where('employer_id', $employerId)
            ->withCount('applications')
            ->get();

        $data = $jobs->map(fn($job) => [
            'id' => $job->id,
            'title' => $job->title,
            'total' => $job->applications_count
        ]);

        return response()->json($data);
    }

    public function revert($id)
    {
        $employer = \App\Models\Employer::findOrFail($id);

        $employer->is_approved = false;
        $employer->status = 'Pending';
        $employer->save();

        return response()->json([
            'message' => 'Employer reverted to Pending successfully.'
        ]);
    }


    // Fetch applications for the dropdown in the schedule modal
    public function applicationsForDropdown()
    {
        $employer = auth()->user()->employer; // kunin ang employer model mula sa user
    $employerId = $employer->id ?? null; // kung wala, null

        $applications = Application::with('beneficiary', 'jobListing')
            ->whereHas('jobListing', fn($q) => $q->where('employer_id', $employerId))
            ->where('status', 'pending') // only pending applications
            ->get()
            ->map(fn($app) => [
                'id' => $app->id,
                'beneficiary_name' => $app->beneficiary->name,
                'job_title' => $app->jobListing->title,
            ]);

        return response()->json($applications);
    }


    public function scheduleInterview(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'start' => 'required|date',
            'summary' => 'nullable|string',
            'attendees' => 'nullable|array',
            'attendees.*' => 'email',
        ]);

        $application = Application::findOrFail($data['application_id']);

        // Create interview
        $interview = Interview::create([
            'application_id' => $application->id,
            'scheduled_at' => $data['start'],
            'summary' => $data['summary'] ?? null,
            'attendees' => $data['attendees'] ?? [],
            'status' => 'Scheduled',
        ]);

        // Optionally: integrate Google Calendar or notifications
        // Example: NotificationService::sendInterviewScheduled($interview);

        return response()->json([
            'message' => 'Interview scheduled successfully',
            'interview' => $interview,
        ]);
    }

public function attendance()
{
    $employer = auth()->user()->employer;

    if (!$employer) {
        return response()->json([]);
    }

    $records = \App\Models\Attendance::where('employer_id', $employer->id)
        ->with('beneficiary.user') // 👈 important para makuha name
        ->latest()
        ->get()
        ->map(function ($r) {
            return [
                'id' => $r->id,
                'date' => $r->date,
                'time_in' => $r->time_in,
                'time_out' => $r->time_out,
                'beneficiary_id' => $r->beneficiary_id,
                'beneficiary_name' => $r->beneficiary?->user?->name ?? 'Unknown',
                'proof' => $r->notes 
                    ? asset('storage/' . $r->notes)
                    : null,
            ];
        }); 

    return response()->json($records);
}

    public function recentActivities()
{
    $employer = auth()->user()->employer;
    if (!$employer) {
        return response()->json([]);
    }


    $attendanceActivities = Attendance::where('employer_id', $employer->id)
        ->latest()
        ->take(5)
        ->get()
        ->map(fn($a) => [
            'type' => 'dtr',
            'title' => 'DTR Submitted',
            'description' => 'Attendance recorded for ' . $a->date,
            'date' => $a->created_at,
            'icon' => '📅'
        ]);


    $ratingActivities = EmployerRating::where('employer_id', $employer->id)
        ->latest()
        ->take(5)
        ->get()
        ->map(fn($r) => [
            'type' => 'rating',
            'title' => 'Rating Submitted',
            'description' => 'Submitted rating for beneficiary ID ' . $r->beneficiary_id,
            'date' => $r->created_at,
            'icon' => '⭐'
        ]);


    $interviewActivities = Interview::where('employer_id', $employer->id)
        ->latest()
        ->take(5)
        ->get()
        ->map(fn($i) => [
            'type' => 'interview',
            'title' => 'Interview Scheduled',
            'description' => 'Interview set for ' . optional($i->scheduled_at)->format('M d, Y H:i'),
            'date' => $i->created_at,
            'icon' => '🎤'
        ]);


    $applicationActivities = Application::whereHas('jobListing', fn($q) => $q->where('employer_id', $employer->id))
        ->latest()
        ->take(5)
        ->get()
        ->map(fn($app) => [
            'type' => 'application',
            'title' => 'Application Status',
            'description' => 'Application is now ' . ucfirst($app->status),
            'date' => $app->updated_at,
            'icon' => '📋'
        ]);


    $jobPostingActivities = JobListing::where('employer_id', $employer->id)
        ->latest()
        ->take(5)
        ->get()
        ->map(fn($job) => [
            'type' => 'job_posting',
            'title' => 'Job Posted',
            'description' => 'Posted job: ' . $job->title,
            'date' => $job->created_at,
            'icon' => '💼'
        ]);


    $reportActivities = Report::where('employer_id', $employer->id)
        ->latest()
        ->take(5)
        ->get()
        ->map(fn($report) => [
            'type' => 'report',
            'title' => 'Report Submitted',
            'description' => 'Submitted report: ' . $report->title,
            'date' => $report->created_at,
            'icon' => '📊'
        ]);


    $activities = collect([
        ...$attendanceActivities,
        ...$ratingActivities,
        ...$interviewActivities,
        ...$applicationActivities,
        ...$jobPostingActivities,
        ...$reportActivities,
    ])->sortByDesc('date')->take(10)->values();


    return response()->json($activities);
}


public function ratingsHistoryData()
{
    $employer = auth()->user()->employer;
    if (!$employer) {
        return response()->json([]);
    }

    $ratings = EmployerRating::where('employer_id', $employer->id)
        ->with('beneficiary')
        ->latest()
        ->get()
        ->map(fn($rating) => [
            'id' => $rating->id,
            'beneficiary_id' => $rating->beneficiary_id,
            'beneficiary_name' => optional($rating->beneficiary)->full_name ?? 'Unknown',
            'punctuality' => $rating->punctuality,
            'work_quality' => $rating->work_quality,
            'attitude' => $rating->attitude,
            'communication' => $rating->communication,
            'overall' => $rating->overall,
            'comment' => $rating->comment,
            'created_at' => $rating->created_at,
        ]);

    return response()->json($ratings);
}


public function allActivities()
{
    $employer = auth()->user()->employer;
    if (!$employer) {
        return response()->json([]);
    }


    $attendanceActivities = Attendance::where('employer_id', $employer->id)
        ->latest()
        ->get()
        ->map(fn($a) => [
            'type' => 'dtr',
            'title' => 'DTR Submitted',
            'description' => 'Attendance recorded for ' . $a->date,
            'date' => $a->created_at,
            'icon' => '📅'
        ]);


    $ratingActivities = EmployerRating::where('employer_id', $employer->id)
        ->latest()
        ->get()
        ->map(fn($r) => [
            'type' => 'rating',
            'title' => 'Rating Submitted',
            'description' => 'Submitted rating for beneficiary ID ' . $r->beneficiary_id,
            'date' => $r->created_at,
            'icon' => '⭐'
        ]);


    $interviewActivities = Interview::where('employer_id', $employer->id)
        ->latest()
        ->get()
        ->map(fn($i) => [
            'type' => 'interview',
            'title' => 'Interview Scheduled',
            'description' => 'Interview set for ' . optional($i->scheduled_at)->format('M d, Y H:i'),
            'date' => $i->created_at,
            'icon' => '🎤'
        ]);


    $applicationActivities = Application::whereHas('jobListing', fn($q) => $q->where('employer_id', $employer->id))
        ->latest()
        ->get()
        ->map(fn($app) => [
            'type' => 'application',
            'title' => 'Application Status',
            'description' => 'Application is now ' . ucfirst($app->status),
            'date' => $app->updated_at,
            'icon' => '📋'
        ]);


    $jobPostingActivities = JobListing::where('employer_id', $employer->id)
        ->latest()
        ->get()
        ->map(fn($job) => [
            'type' => 'job_posting',
            'title' => 'Job Posted',
            'description' => 'Posted job: ' . $job->title,
            'date' => $job->created_at,
            'icon' => '💼'
        ]);


    $reportActivities = Report::where('employer_id', $employer->id)
        ->latest()
        ->get()
        ->map(fn($report) => [
            'type' => 'report',
            'title' => 'Report Submitted',
            'description' => 'Submitted report: ' . $report->title,
            'date' => $report->created_at,
            'icon' => '📊'
        ]);


    $activities = collect([
        ...$attendanceActivities,
        ...$ratingActivities,
        ...$interviewActivities,
        ...$applicationActivities,
        ...$jobPostingActivities,
        ...$reportActivities,
    ])->sortByDesc('date')->values();


    return response()->json($activities);
}


public function storeReport(Request $request)
{
    $data = $request->validate([
        'title' => 'required|string|max:255',
        'body' => 'required|string',
        'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'
    ]);

    $data['employer_id'] = auth()->id();

    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $path = $file->store('reports', 'public');
        $data['file_path'] = $path;
    }

    $report = Report::create($data);

    return response()->json([
        'message' => 'Report submitted to PESO successfully',
        'report' => $report
    ]);
}

    }