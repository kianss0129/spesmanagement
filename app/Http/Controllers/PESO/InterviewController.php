<?php


namespace App\Http\Controllers\PESO;


use App\Http\Controllers\Controller;
use App\Models\Interview;
use App\Models\Application;
use App\Models\Exam; // 🔥 optional (for auto exam)
use App\Services\GoogleCalendarService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class InterviewController extends Controller
{
    /**
     * Schedule Interview
     */
    public function schedule(Request $request)
    {
        // ✅ Validate
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'start' => 'required|date',
            'summary' => 'nullable|string',
            'attendees' => 'nullable|array',
            'meet_link' => 'nullable|url',
        ]);


        $application = Application::with('jobListing')->findOrFail($data['application_id']);
        $beneficiary = $application->beneficiary;


        // 🚫 Prevent scheduling if rejected
        if ($application->status === 'rejected') {
            return back()->withErrors([
                'error' => 'Cannot schedule interview for rejected application.'
            ]);
        }


        // 🚫 Prevent duplicate interview
        $existing = Interview::where('application_id', $application->id)
            ->where('status', 'scheduled')
            ->first();


        if ($existing) {
            return back()->withErrors([
                'error' => 'Interview already scheduled for this application.'
            ]);
        }


        // ⏰ Format times
        $scheduledAt = Carbon::parse($data['start'])->format('Y-m-d H:i:s');
        $startIso = Carbon::parse($data['start'])->toIso8601String();
        $endIso = Carbon::parse($data['start'])->addHour()->toIso8601String();


        // 🔗 Generate meet link
        $link = $data['meet_link'] ?? null;


        if (!$link) {
            try {
                $google = new GoogleCalendarService();


                $event = $google->createInterviewEvent(
                    $data['summary'] ?? 'SPES Interview',
                    $startIso,
                    $endIso,
                    $data['attendees'] ?? [$beneficiary->email]
                );


                $link = $event->hangoutLink ?? null;


            } catch (\Throwable $e) {
                Log::warning('Google Calendar event failed', [
                    'error' => $e->getMessage()
                ]);
            }
        }


        // fallback link
        if (!$link) {
            $link = 'https://meet.google.com/' . substr(md5(uniqid()), 0, 10);
        }


        $jobListing = $application->jobListing;
        $employerId = $jobListing?->employer_id;

        // 💾 Save interview
        $interview = Interview::create([
            'application_id' => $application->id,
            'job_listing_id' => $application->job_listing_id,
            'employer_id' => $employerId,
            'beneficiary_id' => $application->beneficiary_id,
            'scheduled_at' => $scheduledAt,
            'meet_link' => $link,
            'status' => 'scheduled',
            'result' => 'pending', // ✅ important
        ]);


        // 🧾 Log
        activity()
            ->causedBy(auth()->user())
            ->performedOn($interview)
            ->log('Interview scheduled by PESO');


        // 🔔 Notify
        NotificationService::sendInterviewNotification($beneficiary, $interview);


        return back()->with([
            'success' => 'Interview scheduled successfully.',
            'meet_link' => $link
        ]);
    }


    /**
     * Upcoming Interviews
     */
    public function upcoming()
    {
        $interviews = Interview::with(['beneficiary', 'jobListing.employer'])
            ->where('status', 'scheduled') // only active
            ->orderBy('scheduled_at')
            ->take(10)
            ->get()
            ->map(function ($interview) {
                return [
                    'id' => $interview->id,
                    'beneficiary_name' => $interview->beneficiary->first_name
                        ?? $interview->beneficiary->user->name
                        ?? 'N/A',
                    'job_title' => $interview->jobListing?->title ?? 'N/A',
                    'employer_name' => $interview->jobListing?->employer?->name ?? 'N/A',
                    'scheduled_at' => $interview->scheduled_at,
                    'meet_link' => $interview->meet_link,


                    // ✅ REQUIRED FOR UI
                    'status' => $interview->status,
                    'result' => $interview->result,
                ];
            });


        return response()->json($interviews);
    }


    /**
     * Update Interview Result (PASS / FAIL)
     */
    public function updateResult(Request $request, $id)
    {
        $request->validate([
            'result' => 'required|in:passed,failed',
        ]);


        $interview = Interview::findOrFail($id);
        $application = Application::findOrFail($interview->application_id);


        // 🚫 Prevent double update
        if ($interview->status === 'completed') {
            return response()->json([
                'message' => 'Interview already completed'
            ], 400);
        }


        // ✅ Update interview
        $interview->result = $request->result;
        $interview->status = 'completed';
        $interview->save();


        // 🔥 FLOW CONTROL
        if ($request->result === 'passed') {


            $application->status = 'exam';


            // 🔥 OPTIONAL AUTO EXAM
            Exam::create([
                'application_id' => $application->id,
                'exam_date' => now()->addDays(1),
                'location' => 'TBD',
                'status' => 'scheduled',
            ]);


        } else {
            $application->status = 'rejected';
        }


        $application->save();


        // 🧾 Log
        activity()
            ->causedBy(auth()->user())
            ->performedOn($interview)
            ->log('Interview marked as ' . $request->result);


        return response()->json([
            'message' => 'Interview result updated successfully'
        ]);
    }
}

