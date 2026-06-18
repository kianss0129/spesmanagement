<?php

namespace App\Http\Controllers\Peso;

use App\Http\Controllers\Controller;
use App\Mail\BeneficiaryExamScheduleMail;
use App\Mail\ExamResultMail;
use App\Models\Application;
use App\Models\Beneficiary;
use App\Models\Exam;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ExamController extends Controller
{
    public function updateResult(Request $request, $id)
    {
        if (! $this->currentUserCanManageSchedules()) {
            abort(403, 'Only Admin or CPESO Admin users can manage exams.');
        }

        $validated = $request->validate([
            'result' => 'required|in:passed,failed',
        ]);

        $exam = Exam::with('application.beneficiary.user')->findOrFail($id);

        if (! in_array($exam->status, ['scheduled', 'rescheduled'], true)) {
            return response()->json([
                'message' => 'Exam already processed.',
            ], 400);
        }

        DB::transaction(function () use ($exam, $validated) {
            $exam->update([
                'status' => 'completed',
                'result' => $validated['result'],
            ]);

            if ($exam->application) {
                if ($validated['result'] === 'passed') {
                    // Advance directly to for_interview so applicant appears in Interview Scheduler
                    $exam->application->update([
                        'status' => 'for_interview',
                    ]);
                } else {
                    $exam->application->update([
                        'status' => 'rejected',
                    ]);
                }
            }

            $email = optional(optional(optional($exam->application)->beneficiary)->user)->email;

            if ($email) {
                Mail::to($email)->send(new ExamResultMail($exam, $validated['result']));
            }
        });

        $exam->refresh();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($exam)
            ->withProperties([
                'module' => 'Exam',
                'exam_id' => $exam->id,
                'application_id' => $exam->application_id,
                'status' => $exam->status,
                'result' => $validated['result'],
            ])
            ->log('Exam marked as ' . $validated['result']);

        return response()->json([
            'message' => 'Exam result updated successfully.',
        ]);
    }

    public function store(Request $request)
    {
        if (! $this->currentUserCanManageSchedules()) {
            abort(403, 'Only Admin or CPESO Admin users can create schedules.');
        }

        $validated = $request->validate([
            'application_id' => 'nullable',
            'application_ids' => 'nullable|array',
            'application_ids.*' => 'nullable',
            'batch_title' => 'nullable|string|max:255',
            'batch_name' => 'nullable|string|max:255',
            'batch_size' => 'nullable|integer|min:1',
            'exam_date' => 'required|date|after_or_equal:now',
            'end_at' => 'nullable|date|after:exam_date',
            'location' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'instructions' => 'nullable|string',
            'notify_beneficiaries' => 'nullable|boolean',
            'hasScheduledExam' => 'nullable|boolean',
        ]);

        $applicationIds = $this->resolveApplicationIds($validated);

        if (empty($applicationIds)) {
            return response()->json([
                'message' => 'No application selected.',
            ], 422);
        }

        $examDate = Carbon::parse($validated['exam_date']);
        $endAt = ! empty($validated['end_at']) ? Carbon::parse($validated['end_at']) : null;
        $scheduleGroupId = (string) Str::uuid();
        $batchTitle = $validated['batch_title'] ?? $validated['batch_name'] ?? null;
        $notifyBeneficiaries = $request->boolean('notify_beneficiaries', true);

        $scheduledCount = 0;
        $skippedCount = 0;
        $createdExams = [];

        foreach ($applicationIds as $rawApplicationId) {
            DB::transaction(function () use (
                $rawApplicationId,
                $validated,
                $examDate,
                $endAt,
                $scheduleGroupId,
                $batchTitle,
                $notifyBeneficiaries,
                &$scheduledCount,
                &$skippedCount,
                &$createdExams
            ) {
                $application = $this->resolveApplication($rawApplicationId);

                if (! $application) {
                    $skippedCount++;
                    return;
                }

                $alreadyScheduled = Exam::where('application_id', $application->id)
                    ->whereIn('status', ['scheduled', 'rescheduled'])
                    ->exists();

                if ($alreadyScheduled) {
                    $skippedCount++;
                    return;
                }

                $exam = Exam::create([
                    'application_id' => $application->id,
                    'exam_date' => $examDate,
                    'end_at' => $endAt,
                    'location' => $validated['location'],
                    'notes' => $validated['notes'] ?? null,
                    'schedule_group_id' => $scheduleGroupId,
                    'batch_title' => $batchTitle,
                    'scheduled_by' => auth()->id(),
                    'instructions' => $validated['instructions'] ?? null,
                    'notify_beneficiaries' => $notifyBeneficiaries,
                    'status' => 'scheduled',
                    'result' => null,
                ]);

                $application->update([
                    'status' => 'for_exam',
                ]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($exam)
                    ->withProperties([
                        'module' => 'Exam',
                        'exam_id' => $exam->id,
                        'application_id' => $application->id,
                        'beneficiary_id' => $application->beneficiary_id,
                        'schedule_group_id' => $scheduleGroupId,
                        'batch_title' => $batchTitle,
                        'exam_date' => $examDate->toDateTimeString(),
                        'location' => $validated['location'],
                        'status' => 'scheduled',
                    ])
                    ->log('Exam scheduled by PESO');

                $email = optional(optional($application->beneficiary)->user)->email;

                if ($notifyBeneficiaries && $email) {
                    Mail::to($email)->send(
                        new BeneficiaryExamScheduleMail(
                            $application->beneficiary,
                            $examDate->format('F d, Y'),
                            $examDate->format('h:i A'),
                            $validated['location']
                        )
                    );
                }

                $scheduledCount++;
                $createdExams[] = $this->formatExam(
                    $exam->fresh(['application.beneficiary.user', 'scheduledBy:id,name,email'])
                );
            });
        }

        if ($scheduledCount === 0) {
            return response()->json([
                'message' => 'No exams were scheduled. Selected beneficiaries may already have scheduled exams.',
                'scheduled' => $scheduledCount,
                'skipped' => $skippedCount,
                'exams' => $createdExams,
            ], 422);
        }

        return response()->json([
            'message' => $scheduledCount > 1
                ? 'Batch exam scheduled successfully.'
                : 'Exam scheduled successfully.',
            'schedule_group_id' => $scheduleGroupId,
            'batch_title' => $batchTitle,
            'batch_name' => $batchTitle,
            'batch_size' => $validated['batch_size'] ?? count($applicationIds),
            'scheduled' => $scheduledCount,
            'skipped' => $skippedCount,
            'exams' => $createdExams,
        ]);
    }

    public function reschedule(Request $request, Exam $exam)
    {
        if (! $this->currentUserCanManageSchedules()) {
            abort(403, 'Only Admin or CPESO Admin users can reschedule exams.');
        }

        $validated = $request->validate([
            'exam_date' => 'nullable|date',
            'scheduled_at' => 'nullable|date',
            'start' => 'nullable|date',
            'end_at' => 'nullable|date',
            'location' => 'required|string|max:255',
            'reschedule_reason' => 'required|string',
            'instructions' => 'nullable|string',
            'notify_beneficiaries' => 'nullable|boolean',
            'reschedule_scope' => 'nullable|in:single,batch',
        ]);

        $scheduleValue = $validated['exam_date']
            ?? $validated['scheduled_at']
            ?? $validated['start']
            ?? null;

        if (! $scheduleValue) {
            return response()->json([
                'message' => 'Exam date is required for rescheduling.',
            ], 422);
        }

        $targets = $this->resolveRescheduleTargets($exam, $validated['reschedule_scope'] ?? 'single');
        $notifyBeneficiaries = $request->boolean('notify_beneficiaries', true);
        $updated = collect();

        foreach ($targets as $target) {
            $target->update([
                'exam_date' => Carbon::parse($scheduleValue),
                'end_at' => ! empty($validated['end_at']) ? Carbon::parse($validated['end_at']) : $target->end_at,
                'location' => $validated['location'],
                'original_schedule_at' => $target->original_schedule_at ?? $target->exam_date,
                'rescheduled_at' => now(),
                'reschedule_reason' => $validated['reschedule_reason'],
                'instructions' => array_key_exists('instructions', $validated)
                    ? $validated['instructions']
                    : $target->instructions,
                'notify_beneficiaries' => $notifyBeneficiaries,
                'status' => 'rescheduled',
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($target)
                ->withProperties([
                    'module' => 'Exam',
                    'exam_id' => $target->id,
                    'application_id' => $target->application_id,
                    'schedule_group_id' => $target->schedule_group_id,
                    'exam_date' => $target->exam_date?->toDateTimeString(),
                    'location' => $target->location,
                    'reschedule_reason' => $validated['reschedule_reason'],
                    'status' => 'rescheduled',
                ])
                ->log('Exam rescheduled by PESO');

            // TODO: Add dedicated exam reschedule notification content.
            $updated->push($target->fresh(['application.beneficiary.user', 'scheduledBy:id,name,email']));
        }

        return response()->json([
            'message' => $updated->count() > 1
                ? 'Exam batch rescheduled successfully.'
                : 'Exam rescheduled successfully.',
            'exams' => $updated->map(fn (Exam $item) => $this->formatExam($item))->values(),
        ]);
    }

    public function beneficiaryExams()
    {
        $user = auth()->user();
        $beneficiaryId = optional($user->beneficiary)->id;

        if (! $beneficiaryId) {
            return redirect()->back();
        }

        $exams = Exam::with(['application.beneficiary', 'scheduledBy:id,name,email'])
            ->whereHas('application', function ($query) use ($beneficiaryId) {
                $query->where('beneficiary_id', $beneficiaryId);
            })
            ->where('exam_date', '>=', Carbon::now())
            ->orderBy('exam_date', 'asc')
            ->take(5)
            ->get()
            ->map(fn (Exam $exam) => $this->formatExam($exam));

        return inertia('Beneficiary/Exams', [
            'exams' => $exams,
        ]);
    }

    public function upcomingExams()
    {
        if (! $this->currentUserCanManageSchedules()) {
            return response()->json([]);
        }

        $exams = Exam::with(['application.beneficiary.user', 'scheduledBy:id,name,email'])
            ->where('exam_date', '>=', Carbon::now())
            ->orderBy('exam_date', 'asc')
            ->take(5)
            ->get()
            ->map(fn (Exam $exam) => $this->formatExam($exam));

        return response()->json($exams);
    }

    public function apiExams()
    {
        $user = auth()->user();
        $beneficiaryId = optional($user->beneficiary)->id;

        if (! $beneficiaryId) {
            return response()->json([
                'message' => 'Beneficiary profile not found.',
                'exams' => [],
            ], 403);
        }

        $exams = Exam::with(['application.beneficiary', 'scheduledBy:id,name,email'])
            ->whereHas('application', function ($query) use ($beneficiaryId) {
                $query->where('beneficiary_id', $beneficiaryId);
            })
            ->where('exam_date', '>=', Carbon::now())
            ->orderBy('exam_date', 'asc')
            ->take(5)
            ->get()
            ->map(fn (Exam $exam) => $this->formatExam($exam));

        return response()->json($exams);
    }

    private function resolveApplicationIds(array $validated): array
    {
        if (! empty($validated['application_ids']) && is_array($validated['application_ids'])) {
            return collect($validated['application_ids'])
                ->filter(fn ($id) => filled($id))
                ->unique()
                ->values()
                ->all();
        }

        if (! empty($validated['application_id'])) {
            return [$validated['application_id']];
        }

        return [];
    }

    private function resolveApplication($rawApplicationId): ?Application
    {
        if (is_string($rawApplicationId) && str_starts_with($rawApplicationId, 'unassigned_')) {
            $beneficiaryId = str_replace('unassigned_', '', $rawApplicationId);
            $beneficiary = Beneficiary::with('user')->find($beneficiaryId);

            if (! $beneficiary || ! $beneficiary->approved) {
                return null;
            }

            return Application::create([
                'beneficiary_id' => $beneficiary->id,
                'job_listing_id' => null,
                'status' => 'for_exam',
            ]);
        }

        return Application::with('beneficiary.user')->find($rawApplicationId);
    }

    private function resolveRescheduleTargets(Exam $exam, string $scope)
    {
        if ($scope === 'batch' && $exam->schedule_group_id) {
            return Exam::with(['application.beneficiary.user'])
                ->where('schedule_group_id', $exam->schedule_group_id)
                ->get();
        }

        return collect([$exam->load(['application.beneficiary.user'])]);
    }

    private function getBeneficiaryName(?Application $application): string
    {
        $beneficiary = $application?->beneficiary;

        if (! $beneficiary) {
            return 'N/A';
        }

        $fullName = trim(($beneficiary->first_name ?? '') . ' ' . ($beneficiary->last_name ?? ''));

        return $fullName !== ''
            ? $fullName
            : optional($beneficiary->user)->name ?? 'N/A';
    }

    private function formatExam(Exam $exam): array
    {
        return [
            'id' => $exam->id,
            'application_id' => $exam->application_id,
            'beneficiary_name' => $this->getBeneficiaryName($exam->application),
            'exam_date' => $exam->exam_date,
            'end_at' => $exam->end_at,
            'location' => $exam->location ?? 'TBA',
            'status' => $exam->status ?? 'scheduled',
            'result' => $exam->result,
            'notes' => $exam->notes,
            'schedule_group_id' => $exam->schedule_group_id,
            'batch_title' => $exam->batch_title,
            'scheduled_by' => $exam->scheduled_by,
            'scheduled_by_user' => $exam->scheduledBy,
            'interviewer' => null,
            'instructions' => $exam->instructions,
            'original_schedule_at' => $exam->original_schedule_at,
            'rescheduled_at' => $exam->rescheduled_at,
            'reschedule_reason' => $exam->reschedule_reason,
            'notify_beneficiaries' => $exam->notify_beneficiaries,
        ];
    }

    private function currentUserCanManageSchedules(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole(['Admin', 'Super Admin', 'PESO Admin']) ?? false;
    }
}
