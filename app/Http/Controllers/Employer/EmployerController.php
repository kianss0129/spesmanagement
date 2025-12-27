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

class EmployerController extends Controller
{
    // Create Job Listing
    public function storeJob(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'positions' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $data['employer_id'] = auth()->id();
        $job = JobListing::create($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($job)
            ->withProperties($job->toArray())
            ->log('Job created');

        return response()->json(['message' => 'Job created successfully', 'job' => $job]);
    }

    // View Applicants
    public function applicants($jobId)
    {
        return Application::with('beneficiary')
            ->where('job_listing_id', $jobId)
            ->get();
    }

    // Submit DTR / Attendance
    public function submitAttendance(Request $request)
    {
        $data = $request->validate([
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'date' => 'required|date',
            'time_in' => 'nullable',
            'time_out' => 'nullable',
            'notes' => 'nullable|string',
        ]);

        $data['employer_id'] = auth()->id();
        $attendance = Attendance::create($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($attendance)
            ->withProperties($attendance->toArray())
            ->log('Attendance submitted');

        return response()->json(['message' => 'Attendance submitted', 'attendance' => $attendance]);
    }

    // Submit Rating
    public function submitRating(Request $request)
    {
        $payload = $request->validate([
            'employer_id' => 'required|exists:employers,id',
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'application_id' => 'nullable|exists:applications,id',
            'punctuality' => 'nullable|integer|min:1|max:5',
            'work_attitude' => 'nullable|integer|min:1|max:5',
            'output_quality' => 'nullable|integer|min:1|max:5',
            'communication' => 'nullable|integer|min:1|max:5',
            'overall' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $rating = EmployerRating::create($payload);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($rating)
            ->withProperties($rating->toArray())
            ->log('Employer rating submitted');

        return response()->json(['message' => 'Rating submitted', 'rating' => $rating]);
    }

    // Recommended Candidates (for employer view only)
    public function recommendedCandidates()
    {
        $recommended = Beneficiary::withAvg('ratings', 'overall')
            ->withCount([
                'attendances as attendance_present' => function ($q) {
                    $q->whereNotNull('time_in');
                }
            ])
            ->orderByDesc('ratings_avg_overall')
            ->orderByDesc('attendance_present')
            ->limit(10)
            ->get();

        return response()->json($recommended);
    }

    // Return ratings for a specific applicant (beneficiary)
    public function applicantRatings($beneficiaryId)
    {
        return EmployerRating::where('beneficiary_id', $beneficiaryId)->with('employer','application')->get();
    }

    // Choose/assign an applicant for a job (simple flag)
    public function chooseApplicant($jobId, $applicationId)
    {
        $app = Application::where('job_listing_id', $jobId)->where('id', $applicationId)->firstOrFail();
        $app->status = 'selected';
        $app->selected_at = now();
        $app->save();

        return response()->json(['message' => 'Applicant selected', 'application' => $app]);
    }

    // List interviews for this employer
    public function interviews()
    {
        return Interview::where('employer_id', auth()->id())->with(['jobListing','beneficiary'])->get();
    }

    // List attendances or beneficiaries under this employer (simple view)
    public function listAttendance()
    {
        // Show recent attendance records for employer's beneficiaries
        return Attendance::where('employer_id', auth()->id())->with('beneficiary')->orderByDesc('date')->limit(200)->get();
    }

    // Submit work output files (store under storage/app/public/work_outputs)
    public function submitWorkOutput(Request $request)
    {
        $request->validate([
            'beneficiary_id' => 'required|integer',
            'files' => 'required|array',
            'files.*' => 'file|max:10240'
        ]);

        $records = [];
        foreach ($request->file('files') as $f) {
            $path = $f->store('work_outputs', 'public');
            $rec = \App\Models\WorkOutput::create([
                'employer_id' => auth()->id(),
                'beneficiary_id' => $request->input('beneficiary_id'),
                'file_path' => $path,
                'original_name' => $f->getClientOriginalName()
            ]);
            $records[] = $rec;
        }

        activity()->causedBy(auth()->user())->withProperties(['count' => count($records)])->log('Work outputs uploaded');

        return response()->json(['message' => 'Work outputs uploaded', 'records' => $records]);
    }

    // Submit employer report (simple storage)
    public function submitReport(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'body' => 'nullable|string',
        ]);

        $report = \App\Models\Report::create([
            'employer_id' => auth()->id(),
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
        ]);

        activity()->causedBy(auth()->user())->withProperties(['report_id' => $report->id])->log('Employer report submitted');

        return response()->json(['message' => 'Report submitted', 'report' => $report]);
    }

    /**
     * Schedule Interview (Employer action)
     *
     * POST /employer/jobs/{id}/interview
     * body:
     * - beneficiary_id
     * - scheduled_at (ISO datetime)
     */
    public function scheduleInterview(Request $request, $jobId)
    {
        $data = $request->validate([
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:15|max:480',
        ]);

        $job = JobListing::findOrFail($jobId);
        $beneficiary = Beneficiary::findOrFail($data['beneficiary_id']);

        // compute end datetime (default 1 hour)
        $duration = $data['duration_minutes'] ?? 60;
        $start = date('c', strtotime($data['scheduled_at']));
        $end = date('c', strtotime($data['scheduled_at'] . " + {$duration} minutes"));

        // prepare attendees (if beneficiary has email)
        $attendees = [];
        if (!empty($beneficiary->email)) {
            $attendees[] = $beneficiary->email;
        }
        // optionally include employer email if available (assumes Employer model or auth user has email)
        $employerEmail = auth()->user()->email ?? null;
        if ($employerEmail) $attendees[] = $employerEmail;

        // create Google Meet event
        $calendar = new GoogleCalendarService();
        $event = $calendar->createInterviewEvent(
            'Interview: ' . $job->title,
            $start,
            $end,
            $attendees
        );

        // Save interview record
        $interview = Interview::create([
            'application_id' => Application::where('job_listing_id', $job->id)
                                            ->where('beneficiary_id', $beneficiary->id)
                                            ->pluck('id')
                                            ->first(),
            'job_listing_id' => $job->id,
            'employer_id' => auth()->id(),
            'beneficiary_id' => $beneficiary->id,
            'scheduled_at' => $data['scheduled_at'],
            'meet_link' => $event->hangoutLink ?? null,
            'status' => 'scheduled'
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($interview)
            ->withProperties(['job' => $job->id, 'beneficiary' => $beneficiary->id])
            ->log('Interview scheduled');

        // send notifications
        NotificationService::sendInterviewNotification($beneficiary, $interview);

        return response()->json([
            'message' => 'Interview scheduled successfully',
            'interview' => $interview,
            'meet_link' => $event->hangoutLink ?? null,
        ]);
    }

    public function dashboard()
    {
        return Inertia::render('Employer/Dashboard');
    }

    // Analytics: applicants per job for this employer
    public function applicantsPerJob()
    {
        $jobs = JobListing::where('employer_id', auth()->id())
            ->withCount('applications')
            ->get(['id', 'title', 'employer_id']);

        $result = $jobs->map(function ($j) {
            return [
                'id' => $j->id,
                'title' => $j->title,
                'total' => $j->applications_count ?? 0,
            ];
        });

        return response()->json($result);
    }
}
