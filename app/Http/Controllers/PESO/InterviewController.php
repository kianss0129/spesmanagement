<?php

namespace App\Http\Controllers\PESO;

use App\Http\Controllers\Controller;
use App\Mail\InterviewResultMail;
use App\Models\Application;
use App\Models\Interview;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InterviewController extends Controller
{
    public function schedule(Request $request)
    {
        if (! $this->currentUserCanManageSchedules()) {
            abort(403, 'Only Admin or CPESO Admin users can create schedules.');
        }

        $data = $request->validate([
            'application_id' => 'nullable|exists:applications,id',
            'application_ids' => 'nullable|array',
            'application_ids.*' => 'exists:applications,id',
            'batch_title' => 'nullable|string|max:255',
            'batch_name' => 'nullable|string|max:255',
            'interviewer_id' => 'required|exists:users,id',
            'start' => 'required|date',
            'end_at' => 'nullable|date|after:start',
            'summary' => 'nullable|string',
            'attendees' => 'nullable|array',
            'meet_link' => 'required_without:google_meet_link|nullable|url',
            'google_meet_link' => 'nullable|url',
            'instructions' => 'nullable|string',
            'notify_beneficiaries' => 'nullable|boolean',
        ]);

        if (! User::role('PESO')->whereKey($data['interviewer_id'])->exists()) {
            return response()->json([
                'message' => 'The selected interviewer must be a PESO user.',
            ], 422);
        }

        $applicationIds = collect($data['application_ids'] ?? []);

        if (! empty($data['application_id'])) {
            $applicationIds->push($data['application_id']);
        }

        $applicationIds = $applicationIds->unique()->values();

        if ($applicationIds->isEmpty()) {
            return back()->withErrors([
                'error' => 'Please select at least one application to schedule.',
            ]);
        }

        $scheduledAt = Carbon::parse($data['start']);
        $endAt = ! empty($data['end_at'])
            ? Carbon::parse($data['end_at'])
            : $scheduledAt->copy()->addHour();
        $meetLink = $data['meet_link'] ?? $data['google_meet_link'] ?? null;
        $scheduleGroupId = (string) Str::uuid();
        $batchTitle = $data['batch_title'] ?? $data['batch_name'] ?? null;
        $notifyBeneficiaries = $request->boolean('notify_beneficiaries', true);

        $scheduledCount = 0;
        $warnings = [];

        foreach ($applicationIds as $applicationId) {
            $application = Application::with(['beneficiary.user', 'jobListing', 'interview'])->findOrFail($applicationId);
            $beneficiary = $application->beneficiary;

            if ($application->status !== 'for_interview') {
                $warnings[] = "This applicant is not eligible for interview scheduling.";
                continue;
            }

            $hasFinalInterviewResult = Interview::where('application_id', $application->id)
                ->whereIn('result', ['passed', 'failed'])
                ->exists();

            if ($hasFinalInterviewResult) {
                $warnings[] = "This applicant is not eligible for interview scheduling.";
                continue;
            }

            // Cancel any existing stale scheduled interviews for this application
            Interview::where('application_id', $application->id)
                ->where('status', 'scheduled')
                ->update(['status' => 'cancelled']);

            $interview = Interview::create([
                'application_id' => $application->id,
                'job_listing_id' => $application->job_listing_id,
                'employer_id' => $application->jobListing?->employer_id,
                'beneficiary_id' => $application->beneficiary_id,
                'scheduled_at' => $scheduledAt,
                'end_at' => $endAt,
                'meet_link' => $meetLink,
                'schedule_group_id' => $scheduleGroupId,
                'batch_title' => $batchTitle,
                'scheduled_by' => auth()->id(),
                'interviewer_id' => $data['interviewer_id'] ?? null,
                'instructions' => $data['instructions'] ?? null,
                'notify_beneficiaries' => $notifyBeneficiaries,
                'status' => 'scheduled',
                'result' => 'pending',
            ]);

            $application->update([
                'status' => 'for_interview',
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($interview)
                ->log('Interview scheduled by PESO');

            if ($notifyBeneficiaries && $beneficiary) {
                NotificationService::sendInterviewNotification($beneficiary, $interview);
            }

            $scheduledCount++;
        }

        if ($scheduledCount === 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'This applicant is not eligible for interview scheduling.',
                    'warnings' => $warnings,
                ], 422);
            }

            return back()->withErrors([
                'error' => 'No interviews were scheduled. ' . implode(' ', $warnings),
            ]);
        }

        $message = "Scheduled {$scheduledCount} interview" . ($scheduledCount > 1 ? 's' : '') . ' successfully.';

        if ($warnings) {
            $message .= ' Some items were skipped: ' . implode(' ', $warnings);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'schedule_group_id' => $scheduleGroupId,
                'scheduled' => $scheduledCount,
                'warnings' => $warnings,
            ]);
        }

        return back()->with([
            'success' => $message,
        ]);
    }

    public function upcoming()
    {
        $query = Interview::with([
                'beneficiary.user',
                'beneficiary.school',
                'beneficiary.skills',
                'jobListing.employer',
                'application',
                'scheduledBy:id,name,email',
                'interviewer:id,name,email',
            ])
            ->whereIn('status', ['scheduled', 'completed']);

        if (! $this->currentUserCanManageSchedules()) {
            $query->where('interviewer_id', auth()->id());
        }

        $interviews = $query
            ->orderBy('scheduled_at', 'desc')
            ->get()
            ->map(fn (Interview $interview) => $this->formatInterview($interview));

        return response()->json($interviews);
    }

    public function assigned()
    {
        $interviews = Interview::with([
                'beneficiary.user',
                'beneficiary.school',
                'beneficiary.skills',
                'jobListing.employer',
                'application',
                'scheduledBy:id,name,email',
                'interviewer:id,name,email',
            ])
            ->where('interviewer_id', auth()->id())
            ->whereIn('status', ['scheduled', 'completed'])
            ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn (Interview $interview) => $this->formatInterview($interview));

        return response()->json($interviews);
    }

    public function reschedule(Request $request, Interview $interview)
    {
        if (! $this->currentUserCanManageSchedules()) {
            abort(403, 'Only Admin or CPESO Admin users can reschedule interviews.');
        }

        $validated = $request->validate([
            'scheduled_at' => 'nullable|date',
            'start' => 'nullable|date',
            'end_at' => 'nullable|date',
            'meet_link' => 'nullable|url',
            'google_meet_link' => 'nullable|url',
            'interviewer_id' => 'nullable|exists:users,id',
            'reschedule_reason' => 'required|string',
            'instructions' => 'nullable|string',
            'notify_beneficiaries' => 'nullable|boolean',
            'reschedule_scope' => 'nullable|in:single,batch',
        ]);

        if (
            ! empty($validated['interviewer_id']) &&
            ! User::role('PESO')->whereKey($validated['interviewer_id'])->exists()
        ) {
            return response()->json([
                'message' => 'The selected interviewer must be a PESO user.',
            ], 422);
        }

        $scheduledAtValue = $validated['scheduled_at'] ?? $validated['start'] ?? null;

        if (! $scheduledAtValue && ! $request->filled('meet_link') && ! $request->filled('google_meet_link')) {
            return response()->json([
                'message' => 'Provide a new schedule date/time or meeting link to reschedule.',
            ], 422);
        }

        $targets = $this->resolveRescheduleTargets($interview, $validated['reschedule_scope'] ?? 'single');
        $notifyBeneficiaries = $request->boolean('notify_beneficiaries', true);
        $updated = collect();

        foreach ($targets as $target) {
            $newScheduledAt = $scheduledAtValue
                ? Carbon::parse($scheduledAtValue)
                : $target->scheduled_at;

            $target->update([
                'scheduled_at' => $newScheduledAt,
                'end_at' => ! empty($validated['end_at']) ? Carbon::parse($validated['end_at']) : $target->end_at,
                'meet_link' => $validated['meet_link'] ?? $validated['google_meet_link'] ?? $target->meet_link,
                'interviewer_id' => $validated['interviewer_id'] ?? $target->interviewer_id,
                'original_schedule_at' => $target->original_schedule_at ?? $target->scheduled_at,
                'rescheduled_at' => now(),
                'reschedule_reason' => $validated['reschedule_reason'],
                'instructions' => array_key_exists('instructions', $validated)
                    ? $validated['instructions']
                    : $target->instructions,
                'notify_beneficiaries' => $notifyBeneficiaries,
                // The current interviews.status column is an enum: scheduled/completed/cancelled.
                'status' => 'scheduled',
            ]);

            // Ensure interview is visible immediately: reset result to pending
            try {
                $target->result = 'pending';
                $target->save();
            } catch (\Throwable $e) {
                // ignore save issues here but continue
            }

            // Make sure the related application is set back to for_interview
            try {
                if ($target->application_id) {
                    $app = Application::find($target->application_id);
                    if ($app && ! in_array($app->status, ['interview_passed', 'rejected', 'completed', 'for_interview'])) {
                        $app->status = 'for_interview';
                        $app->save();
                    } elseif ($app && $app->status !== 'for_interview') {
                        // if previously moved to another interim status, ensure for_interview
                        $app->status = 'for_interview';
                        $app->save();
                    }
                }
            } catch (\Throwable $e) {
                // non-blocking
            }

            // TODO: Add dedicated interview reschedule notification content.
            if ($notifyBeneficiaries && $target->beneficiary) {
                NotificationService::sendInterviewNotification($target->beneficiary, $target);
            }

            $updated->push($target->fresh([
                'beneficiary.user',
                'jobListing.employer',
                'scheduledBy:id,name,email',
                'interviewer:id,name,email',
            ]));
        }

        return response()->json([
            'message' => $updated->count() > 1
                ? 'Interview batch rescheduled successfully.'
                : 'Interview rescheduled successfully.',
            'interviews' => $updated->map(fn (Interview $item) => $this->formatInterview($item))->values(),
        ]);
    }

    public function updateResult(Request $request, $id)
    {
        $request->validate([
            'result' => 'required|in:passed,failed,needs_review',
            'remarks' => 'required|string|max:2000',
        ]);

        $interview = Interview::with('beneficiary')->findOrFail($id);

        if (
            ! $this->currentUserCanManageSchedules() &&
            (int) $interview->interviewer_id !== (int) auth()->id()
        ) {
            abort(403, 'You can only update interviews assigned to you.');
        }

        $application = Application::findOrFail($interview->application_id);

        if ($interview->status === 'completed') {
            return response()->json([
                'message' => 'Interview already completed',
            ], 400);
        }

        $interview->result = $request->result;
        $interview->remarks = $request->remarks;
        $interview->evaluated_at = now();
        $interview->status = 'completed';
        $interview->save();

        if ($request->result === 'passed') {
            $application->status = 'interview_passed';
        } elseif ($request->result === 'failed') {
            $application->status = 'rejected';
        } else {
            $application->status = 'needs_review';
        }

        $application->save();

        if ($request->result !== 'needs_review' && $interview->beneficiary?->email) {
            Mail::to($interview->beneficiary->email)->send(
                new InterviewResultMail($interview, $request->result)
            );
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($interview)
            ->log('Interview marked as ' . $request->result);

        return response()->json([
            'message' => 'Interview result updated successfully',
            'interview' => $this->formatInterview($interview->fresh([
                'beneficiary.user',
                'beneficiary.school',
                'beneficiary.skills',
                'jobListing.employer',
                'application',
                'scheduledBy:id,name,email',
                'interviewer:id,name,email',
            ])),
        ]);
    }

    private function resolveRescheduleTargets(Interview $interview, string $scope)
    {
        if ($scope === 'batch' && $interview->schedule_group_id) {
            return Interview::with(['beneficiary.user', 'jobListing.employer'])
                ->where('schedule_group_id', $interview->schedule_group_id)
                ->get();
        }

        return collect([$interview->load(['beneficiary.user', 'jobListing.employer'])]);
    }

    private function formatInterview(Interview $interview): array
    {
        $beneficiary = $interview->beneficiary;
        $documents = $this->summarizeDocuments($beneficiary?->documents);

        return [
            'id' => $interview->id,
            'application_id' => $interview->application_id,
            'beneficiary_id' => $interview->beneficiary_id,
            'beneficiary_name' => trim(implode(' ', array_filter([
                    $beneficiary?->first_name,
                    $beneficiary?->middle_name,
                    $beneficiary?->last_name,
                ]))) ?: $beneficiary?->user?->name
                ?? 'N/A',
            'beneficiary_profile' => [
                'id' => $beneficiary?->id,
                'name' => trim(implode(' ', array_filter([
                    $beneficiary?->first_name,
                    $beneficiary?->middle_name,
                    $beneficiary?->last_name,
                ]))) ?: $beneficiary?->user?->name,
                'category' => $beneficiary?->category ?? $beneficiary?->user?->beneficiary_type,
                'school' => $beneficiary?->school?->name ?? $beneficiary?->school_name,
                'course' => $beneficiary?->course ?? $beneficiary?->program,
                'skills' => $beneficiary?->skills?->map(fn ($skill) => $skill->name)->filter()->values() ?? [],
                'requirements_summary' => $documents,
            ],
            'job_title' => $interview->jobListing?->title ?? 'N/A',
            'employer_name' => $interview->jobListing?->employer?->name ?? 'N/A',
            'application_status' => $interview->application?->status,
            'scheduled_at' => $interview->scheduled_at,
            'end_at' => $interview->end_at,
            'meet_link' => $interview->meet_link,
            'schedule_group_id' => $interview->schedule_group_id,
            'batch_title' => $interview->batch_title,
            'scheduled_by' => $interview->scheduled_by,
            'scheduled_by_user' => $interview->scheduledBy,
            'interviewer_id' => $interview->interviewer_id,
            'interviewer' => $interview->interviewer,
            'instructions' => $interview->instructions,
            'original_schedule_at' => $interview->original_schedule_at,
            'rescheduled_at' => $interview->rescheduled_at,
            'reschedule_reason' => $interview->reschedule_reason,
            'notify_beneficiaries' => $interview->notify_beneficiaries,
            'status' => $interview->status,
            'result' => $interview->result,
            'remarks' => $interview->remarks,
            'evaluated_at' => $interview->evaluated_at,
        ];
    }

    public function formatForPesoUser(Interview $interview): array
    {
        return $this->formatInterview($interview);
    }

    private function summarizeDocuments($documents): array
    {
        $items = is_array($documents) ? $documents : [];

        return [
            'total' => count($items),
            'available' => collect($items)->filter(function ($document) {
                if (is_string($document)) {
                    return filled($document);
                }

                if (is_array($document)) {
                    return filled($document['path'] ?? $document['file'] ?? null);
                }

                return false;
            })->count(),
        ];
    }

    private function currentUserCanManageSchedules(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole(['Admin', 'Super Admin', 'PESO Admin']) ?? false;
    }
}
