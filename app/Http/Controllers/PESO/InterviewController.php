<?php

namespace App\Http\Controllers\PESO;

use App\Http\Controllers\Controller;
use App\Models\Interview;
use App\Models\Application;
use App\Services\GoogleCalendarService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    public function schedule(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'summary' => 'nullable|string',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'attendees' => 'nullable|array',
        ]);

        $application = Application::findOrFail($data['application_id']);
        $beneficiary = $application->beneficiary;

        $start = date('c', strtotime($data['start']));
        $end = isset($data['end']) && $data['end'] ? date('c', strtotime($data['end'])) : date('c', strtotime($data['start'] . ' +1 hour'));

        $link = null;
        try {
            $google = new GoogleCalendarService();
            $event = $google->createInterviewEvent(
                $data['summary'] ?? 'SPES Interview',
                $start,
                $end,
                $data['attendees'] ?? [$beneficiary->email]
            );
            $link = $event->hangoutLink ?? null;
        } catch (\Throwable $e) {
            // Don't fail tests if Google credentials are missing; proceed without a meet link
            \Illuminate\Support\Facades\Log::warning('google-event-failed', ['error' => $e->getMessage()]);
            $link = null;
        }

        $interview = Interview::create([
            'application_id' => $application->id,
            'job_listing_id' => $application->job_listing_id,
            'employer_id' => $application->jobListing->employer_id ?? null,
            'beneficiary_id' => $application->beneficiary_id,
            'scheduled_at' => $data['start'],
            'meet_link' => $link,
            'status' => 'scheduled',
        ]);

        activity()->causedBy(auth()->user())->performedOn($interview)->log('Interview scheduled by PESO');

        NotificationService::sendInterviewNotification($beneficiary, $interview);

        return response()->json(['message' => 'Interview scheduled', 'interview' => $interview, 'meet_link' => $event->hangoutLink ?? null]);
    }
}
