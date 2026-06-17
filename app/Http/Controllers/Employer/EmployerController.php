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
    use Illuminate\Support\Facades\Storage;
    use App\Models\Announcement;
    use App\Models\Report;
    use App\Models\WorkOutput;


    class EmployerController extends Controller
    {


public function reportHistory()
{
    $employer = auth()->user()->employer;

    if (! $employer) {
        return response()->json(['reports' => []]);
    }

    $reports = Report::where('employer_id', $employer->id)
        ->latest()
        ->get();


    return response()->json([
        'reports' => $reports
    ]);
}






    public function acknowledgeAssignment(Application $application)
    {
        $this->authorizeEmployerApplication($application);

        if (! in_array($application->status, ['contract_signed', 'assigned', 'deployed', 'ongoing', 'completion_review'], true)) {
            return response()->json([
                'message' => 'Only beneficiaries with signed contracts can be deployed.',
            ], 422);
        }

        $application->update([
            'employer_acknowledged_at' => $application->employer_acknowledged_at ?? now(),
            'employer_acknowledged_by' => $application->employer_acknowledged_by ?? auth()->id(),
            'status' => in_array($application->status, ['contract_signed', 'assigned']) ? 'deployed' : $application->status,
        ]);

        return response()->json([
            'message' => 'Beneficiary deployed successfully. Work supervision may begin.',
            'application' => $this->formatEmployerApplication(
                $application->fresh(['jobListing', 'beneficiary.user', 'beneficiary.school'])
            ),
        ]);
    }


    public function updateJobStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:ongoing,completion_review,completed',
        ]);

        $application = Application::with('jobListing')->findOrFail($id);
        $this->authorizeEmployerApplication($application);

        $status = $validated['status'] === 'completed'
            ? 'completion_review'
            : $validated['status'];

        if ($status === 'completion_review') {
            if (! $application->employer_acknowledged_at) {
                return response()->json([
                    'message' => 'Acknowledge the assignment before submitting completion for CPESO review.',
                ], 422);
            }

            if (! in_array($application->status, ['deployed', 'ongoing', 'completion_review'], true)) {
                return response()->json([
                    'message' => 'Only active assignments can be submitted for completion review.',
                ], 422);
            }
        }

        $application->status = $status;
        $application->save();


        return response()->json([
            'message' => $status === 'completion_review'
                ? 'Completion submitted for CPESO review.'
                : 'Status updated successfully',
            'status' => $status,
        ]);
    }


    public function uploadCertificate(Request $request, $id)
    {
        $request->validate([
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);


        try {
            $application = Application::with('jobListing')->findOrFail($id);
            $this->authorizeEmployerApplication($application);
           
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
        $user = auth()->user();


        $announcements = Announcement::forRole('employer')
            ->latest()
            ->get()
            ->map(function ($announcement) use ($user) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'image' => $announcement->image,
                    'target_role' => $announcement->target_role,
                    'created_at' => $announcement->created_at,
                    'read' => $announcement->isReadBy($user),
                ];
            });


        return response()->json($announcements);
    }


    public function markAllNotificationsAsRead()
    {
        $user = auth()->user();
        $announcements = Announcement::forRole('employer')->get();


        foreach ($announcements as $announcement) {
            $announcement->markAsReadBy($user);
        }


        return response()->json(['message' => 'All notifications marked as read']);
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

            try {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($job)
                    ->withProperties([
                        'module' => 'Employer',
                        'user_id' => auth()->id(),
                        'status' => $job->status ?? 'posted',
                    ])
                    ->log('Employer posted job');
            } catch (\Throwable $e) {
                report($e);
            }


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
        ->map(fn ($app) => $this->formatEmployerApplication($app))
        ->groupBy(fn($app) => $app['job_title'] ?? 'Unassigned');


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
                    // attach the application if exists so attendance links to specific assignment
                    $application = Application::where('beneficiary_id', $rec['beneficiary_id'])
                        ->whereHas('jobListing', fn($q) => $q->where('employer_id', auth()->id()))
                        ->latest()
                        ->first();
                    if ($application) {
                        $rec['application_id'] = $application->id;
                    }
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
            $application = Application::where('beneficiary_id', $data['beneficiary_id'])
                ->whereHas('jobListing', fn($q) => $q->where('employer_id', auth()->id()))
                ->latest()
                ->first();
            if ($application) {
                $data['application_id'] = $application->id;
            }
            $attendance=Attendance::create($data);


            return response()->json(['message'=>'Attendance submitted','attendance'=>$attendance]);
        }


        // Submit rating
    public function submitRating(Request $request, $jobId = null, $beneficiaryId = null)
{
    $validated = $request->validate([
        'application_id' => 'nullable|exists:applications,id',
        'beneficiary_id' => 'nullable|exists:beneficiaries,id',
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

    $resolvedBeneficiaryId = $request->beneficiary_id ?? $beneficiaryId;
    $application = Application::with('jobListing')
        ->when($validated['application_id'] ?? null, fn ($query, $applicationId) => $query->where('id', $applicationId))
        ->when($jobId, fn ($query) => $query->where('job_listing_id', $jobId))
        ->when($resolvedBeneficiaryId, fn ($query) => $query->where('beneficiary_id', $resolvedBeneficiaryId))
        ->whereHas('jobListing', fn ($query) => $query->where('employer_id', $employer->id))
        ->latest()
        ->first();

    if (! $application) {
        return response()->json([
            'message' => 'You can only rate beneficiaries assigned to your employer account.',
        ], 403);
    }


    $rating = EmployerRating::create([
        'application_id' => $application->id,
        'beneficiary_id' => $application->beneficiary_id,
        'employer_id' => $employer->id,
        'punctuality' => $request->punctuality,
        'work_quality' => $request->work_quality,
        'attitude' => $request->work_attitude,
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
        return response()->json([]);
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
            $user = auth()->user();
            $employer = $user->employer;


            $details = $employer?->details ?? [];
            $contactPersonName = $employer?->contact_person ?: trim(implode(' ', array_filter([
                data_get($details, 'contact_person.first_name'),
                data_get($details, 'contact_person.middle_name'),
                data_get($details, 'contact_person.last_name'),
                data_get($details, 'contact_person.suffix'),
            ])));


            $companyPhone = $employer?->phone ?: data_get($details, 'company_contact.mobile_number');
            $companyAddress = $employer?->address ?: trim(implode(', ', array_filter([
                data_get($details, 'house_number'),
                data_get($details, 'street'),
                data_get($details, 'barangay'),
                data_get($details, 'city'),
                data_get($details, 'province'),
                data_get($details, 'zip_code'),
            ])));


            $profileComplete = filled($employer?->company_name)
                && filled($contactPersonName)
                && filled($companyPhone)
                && filled($companyAddress);


            $documents = is_array($employer?->documents)
                ? $employer->documents
                : ($employer?->documents ? [$employer->documents] : []);


            $documentsUploaded = count($documents) > 0;
            $emailVerified = $user->hasVerifiedEmail();
            $approved = $employer?->approval_status === 'approved';


            $steps = [
                ['label' => 'Verify your email', 'complete' => $emailVerified],
                ['label' => 'Complete company profile', 'complete' => $profileComplete],
                ['label' => 'Upload required documents', 'complete' => $documentsUploaded],
                ['label' => 'Await admin approval', 'complete' => $approved],
            ];


            $completedSteps = collect($steps)->where('complete', true)->count();
            $percentage = (int) floor(($completedSteps / count($steps)) * 100);


            return Inertia::render('Employer/Dashboard', [
                'employer' => $employer,
                'onboardingProgress' => [
                    'percentage' => $percentage,
                    'steps' => $steps,
                    'status' => $approved ? 'approved' : ($emailVerified && $profileComplete && $documentsUploaded ? 'pending' : 'incomplete'),
                ],
                'limitedAccess' => !$approved || ! $profileComplete || ! $documentsUploaded,
            ]);
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
            'work_quality' => $ratingCount ? round($ratingQuery->avg('output_quality'), 1) : 0,
            'attitude' => $ratingCount ? round($ratingQuery->avg('work_attitude'), 1) : 0,
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
            'pending_applicants' => Application::whereHas('jobListing', fn($q) => $q->where('employer_id', $employerId))
                ->whereIn('status', ['contract_signed', 'deployed', 'ongoing'])
                ->count(),
            'pending_dtrs' => Attendance::where('employer_id', $employerId)
                ->where(function ($q) {
                    $q->whereNull('review_status')
                      ->orWhere('review_status', 'pending');
                })
                ->count(),
            'upcoming_interviews' => Interview::whereHas('application.jobListing', fn($q) => $q->where('employer_id', $employerId))
                ->where('scheduled_at', '>=', now())
                ->count(),
            'pending_ratings' => Application::whereHas('jobListing', fn($q) => $q->where('employer_id', $employerId))
                ->whereIn('status', ['deployed', 'ongoing', 'completion_review'])
                ->whereDoesntHave('employerRating')
                ->count(),
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
        return response()->json([
            'message' => 'Interview scheduling is managed by CPESO/PESO, not employers.',
        ], 403);
    }


public function attendance()
{
    $employer = auth()->user()->employer;
    if (!$employer) {
        $employer = \App\Models\Employer::where('user_id', auth()->id())->first();
    }


    if (!$employer) {
        return response()->json([]);
    }
    // Load attendance records and use the stored application link so historical records
    // remain tied to the application where they were created.
    $records = \App\Models\Attendance::whereIn('employer_id', [$employer->id, auth()->id()])
        ->with(['beneficiary.user', 'application.jobListing', 'reviewedBy'])
        ->latest()
        ->get()
        ->map(function ($r) use ($employer) {
            $app = $r->application;
            $jobListingId = $app?->job_listing_id ?? null;
            $jobTitle = $app?->jobListing?->title ?? null;
            $jobStatus = $app?->status ?? null;
            $notes = [];
            $rawNotes = $r->notes ? trim($r->notes) : null;

            if ($rawNotes) {
                $decodedNotes = json_decode($rawNotes, true);
                $notes = is_array($decodedNotes) ? $decodedNotes : ['in' => $rawNotes, 'out' => null];
            }

            $proofUrl = function (?string $path): ?string {
                if (! $path) {
                    return null;
                }

                $path = trim($path);

                if ($path === '') {
                    return null;
                }

                if (preg_match('/^https?:\/\//i', $path) || str_starts_with($path, '/storage/')) {
                    return $path;
                }

                if (str_starts_with($path, 'storage/')) {
                    $path = substr($path, strlen('storage/'));
                }

                return Storage::url(ltrim($path, '/'));
            };


            // If attendance doesn't have an application link, try to infer the job
            // that applied at the time of the attendance (created_at <= attendance.date).
            if (!$app) {
                $fallbackApp = Application::where('beneficiary_id', $r->beneficiary_id)
                    ->whereHas('jobListing', fn($q) => $q->where('employer_id', $employer->id))
                    ->where('created_at', '<=', $r->date)
                    ->with('jobListing')
                    ->orderByDesc('created_at')
                    ->first();


                if ($fallbackApp) {
                    $jobListingId = $fallbackApp->job_listing_id;
                    $jobTitle = $fallbackApp->jobListing?->title ?? 'Unknown Job';
                    $jobStatus = $fallbackApp->status;
                    $app = $fallbackApp;
                } else {
                    // Last resort: use latest application for this beneficiary-employer
                    $latestApp = Application::where('beneficiary_id', $r->beneficiary_id)
                        ->whereHas('jobListing', fn($q) => $q->where('employer_id', $employer->id))
                        ->with('jobListing')
                        ->latest('updated_at')
                        ->first();


                    if ($latestApp) {
                        $jobListingId = $latestApp->job_listing_id;
                        $jobTitle = $latestApp->jobListing?->title ?? 'Unknown Job';
                        $jobStatus = $latestApp->status;
                        $app = $latestApp;
                    }
                }
            }


            $beneficiaryName = trim(
                ($r->beneficiary?->first_name ?? '') . ' ' . ($r->beneficiary?->last_name ?? '')
            );


            return [
                'id' => $r->id,
                'date' => $r->date,
                'time_in' => $r->time_in,
                'time_out' => $r->time_out,
                'beneficiary_id' => $r->beneficiary_id,
                'beneficiary_name' => $beneficiaryName ?: ($r->beneficiary?->user?->name ?? 'Unknown'),
                'job_listing_id' => $jobListingId,
                'job_title' => $jobTitle ?? 'No Job Assigned',
                'job_status' => $jobStatus,
                'has_application' => (bool) $app,
                'notes_in' => $notes['in'] ?? null,
                'notes_out' => $notes['out'] ?? null,
                'time_in_proof_url' => $proofUrl($notes['in'] ?? null),
                'time_out_proof_url' => $proofUrl($notes['out'] ?? null),
                'proof' => $proofUrl($notes['in'] ?? null),
                'review_status' => $r->review_status ?? 'pending',
                'review_remarks' => $r->review_remarks,
                'reviewed_at' => $r->reviewed_at,
                'reviewed_by' => $r->reviewedBy?->name,
            ];
        });


    return response()->json($records);
}


    public function reviewAttendance(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'review_status' => 'required|string|in:approved,needs_correction,rejected',
            'review_remarks' => 'nullable|string',
        ]);

        if (in_array($validated['review_status'], ['needs_correction', 'rejected'], true)
            && blank($validated['review_remarks'] ?? null)) {
            return response()->json([
                'message' => 'Remarks are required when requesting correction or rejecting DTR.',
            ], 422);
        }

        $this->authorizeEmployerAttendance($attendance);

        $attendance->update([
            'review_status' => $validated['review_status'],
            'review_remarks' => $validated['review_remarks'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'DTR review saved.',
            'attendance' => $attendance->fresh(['beneficiary.user', 'application.jobListing', 'reviewedBy']),
        ]);
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


    $employer = auth()->user()->employer;

    if (! $employer) {
        return response()->json(['message' => 'Employer not found.'], 403);
    }

    $data['employer_id'] = $employer->id;


    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $path = $file->store('reports', 'public');
        $data['file_path'] = $path;
    }


    $report = Report::create($data);

    try {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($report)
            ->withProperties([
                'module' => 'Employer',
                'user_id' => auth()->id(),
                'status' => 'submitted',
            ])
            ->log('Employer submitted report');
    } catch (\Throwable $e) {
        report($e);
    }


    return response()->json([
        'message' => 'Report submitted to PESO successfully',
        'report' => $report
    ]);
}

    private function authorizeEmployerApplication(Application $application): void
    {
        $employer = auth()->user()->employer;

        if (! $employer) {
            abort(403, 'Employer account not found.');
        }

        $application->loadMissing('jobListing');

        if ((int) $application->jobListing?->employer_id !== (int) $employer->id) {
            abort(403, 'You can only manage beneficiaries assigned to your employer account.');
        }
    }

    private function authorizeEmployerAttendance(Attendance $attendance): void
    {
        $employer = auth()->user()->employer;

        if (! $employer) {
            abort(403, 'Employer account not found.');
        }

        $attendance->loadMissing('application.jobListing', 'beneficiary');

        $directEmployerMatch = (int) $attendance->employer_id === (int) $employer->id;
        $applicationEmployerMatch = (int) $attendance->application?->jobListing?->employer_id === (int) $employer->id;
        $beneficiaryEmployerMatch = (int) $attendance->beneficiary?->employer_id === (int) $employer->id;

        if (! $directEmployerMatch && ! $applicationEmployerMatch && ! $beneficiaryEmployerMatch) {
            abort(403, 'You can only review DTR records for beneficiaries assigned to your employer account.');
        }
    }

    private function formatEmployerApplication(Application $application): array
    {
        $application->loadMissing('jobListing', 'beneficiary.user', 'beneficiary.school');

        $beneficiary = $application->beneficiary;
        $beneficiaryName = trim(($beneficiary?->first_name ?? '') . ' ' . ($beneficiary?->last_name ?? ''));

        // Count attendance records and approved ones for DTR readiness
        $attendanceCount = Attendance::where('application_id', $application->id)->count();
        $approvedAttendanceCount = Attendance::where('application_id', $application->id)
            ->where('review_status', 'approved')
            ->count();

        // Check if employer has submitted a rating for this application
        $hasRating = \App\Models\EmployerRating::where('application_id', $application->id)->exists();

        // Check daily reports / work outputs
        $workOutputsCount = \App\Models\WorkOutput::where('application_id', $application->id)->count() ?? 0;
        $approvedWorkOutputsCount = \App\Models\WorkOutput::where('application_id', $application->id)
            ->where('status', 'approved')
            ->count() ?? 0;

        return [
            'id' => $application->id,
            'beneficiary_id' => $application->beneficiary_id,
            'job_listing_id' => $application->job_listing_id,
            'job_title' => $application->jobListing?->title ?? 'Unassigned',
            'status' => $application->status,
            'created_at' => $application->created_at,
            'updated_at' => $application->updated_at,
            'certificate_path' => $application->certificate_path ?? null,
            'employer_acknowledged_at' => $application->employer_acknowledged_at,
            'employer_acknowledged_by' => $application->employer_acknowledged_by,
            'assignment_status' => $this->assignmentStatusLabel($application),
            'attendance_count' => $attendanceCount,
            'approved_attendance_count' => $approvedAttendanceCount,
            'dtr_reviewed' => $approvedAttendanceCount > 0,
            'work_outputs_count' => $workOutputsCount,
            'approved_work_outputs_count' => $approvedWorkOutputsCount,
            'daily_reports_reviewed' => $approvedWorkOutputsCount > 0,
            'rating_submitted' => $hasRating,
            'beneficiary' => $beneficiary ? [
    'id' => $beneficiary->id,

    'first_name' => $beneficiary->first_name,
    'middle_name' => $beneficiary->middle_name,
    'last_name' => $beneficiary->last_name,
    'suffix' => $beneficiary->suffix,

    'full_name' => $beneficiaryName ?: ($beneficiary->user?->name ?? 'Unknown Beneficiary'),

    'email' => $beneficiary->email ?: $beneficiary->user?->email,
    'phone' => $beneficiary->phone,
    'contact_number' => $beneficiary->contact_number,

    'category' => $beneficiary->category,

    'school_name' => $beneficiary->school_name,
    'school_address' => $beneficiary->school_address,
    'education_level' => $beneficiary->education_level,
    'school_year' => $beneficiary->school_year,
    'year_level' => $beneficiary->year_level,
    'course' => $beneficiary->course,

    'last_school_attended' => $beneficiary->last_school_attended,
    'highest_attainment' => $beneficiary->highest_attainment,
    'year_last_attended' => $beneficiary->year_last_attended,

    'parent_guardian_name' => $beneficiary->parent_guardian_name,
    'parent_name' => $beneficiary->parent_name,
    'relationship' => $beneficiary->relationship,

    'former_employer' => $beneficiary->former_employer,
    'displacement_reason' => $beneficiary->displacement_reason,
    'displacement_date' => $beneficiary->displacement_date,

    'present_address' => $beneficiary->present_address,
    'barangay' => $beneficiary->barangay,
    'city' => $beneficiary->city,
    'province' => $beneficiary->province,

    'family_income' => $beneficiary->family_income,

    'created_at' => $beneficiary->created_at,

    'school' => $beneficiary->school?->name,

    'profile_photo_url' => $beneficiary->user?->profile_photo_path
        ? asset('storage/' . $beneficiary->user->profile_photo_path)
        : '/default-profile.png',
] : null,
        ];
    }

    private function assignmentStatusLabel(Application $application): string
    {
        if (! $application->employer_acknowledged_at && $application->status === 'assigned') {
            return 'Pending Acknowledgement';
        }

        return match ($application->status) {
            'assigned' => 'Acknowledged',
            'deployed', 'ongoing' => 'Active',
            'completion_review' => 'Completion Review',
            'completed' => 'Completed',
            default => ucfirst(str_replace('_', ' ', $application->status ?? 'pending')),
        };
    }


    }
