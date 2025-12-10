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
}
