<?php

namespace App\Http\Controllers\PESO;

use App\Http\Controllers\Controller;
use App\Mail\ContractSigningMail;
use App\Models\Application;
use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ContractController extends Controller
{
    public function updateResult(Request $request, $id)
    {
        if (! $this->currentUserCanManageSchedules()) {
            abort(403, 'Only Admin or CPESO Admin users can manage contract signing.');
        }

        $validated = $request->validate([
            'result' => 'required|in:signed,not_signed',
        ]);

        $contract = Contract::with('application')->findOrFail($id);

        if (! in_array($contract->status, ['scheduled', 'rescheduled'], true)) {
            return response()->json([
                'message' => 'Contract already processed.',
            ], 400);
        }

        DB::transaction(function () use ($contract, $validated) {
            $contract->update([
                'status' => 'completed',
                'result' => $validated['result'],
            ]);

            if ($validated['result'] === 'signed') {
                $contract->application->update([
                    'status' => 'contract_signed',
                ]);
            } else {
                $contract->application->update([
                    'status' => 'rejected',
                ]);
            }
        });

        return response()->json([
            'message' => 'Contract result updated successfully',
        ]);
    }

    public function store(Request $request)
    {
        if (! $this->currentUserCanManageSchedules()) {
            abort(403, 'Only Admin or CPESO Admin users can create schedules.');
        }

        $validated = $request->validate([
            'application_id' => 'nullable|exists:applications,id',
            'application_ids' => 'nullable|array',
            'application_ids.*' => 'exists:applications,id',
            'batch_title' => 'nullable|string|max:255',
            'batch_name' => 'nullable|string|max:255',
            'contract_date' => 'required|date|after_or_equal:now',
            'end_at' => 'nullable|date|after:contract_date',
            'location' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'instructions' => 'nullable|string',
            'notify_beneficiaries' => 'nullable|boolean',
        ]);

        $applicationIds = collect($validated['application_ids'] ?? []);

        if (! empty($validated['application_id'])) {
            $applicationIds->push($validated['application_id']);
        }

        $applicationIds = $applicationIds->unique()->values();

        if ($applicationIds->isEmpty()) {
            return response()->json([
                'message' => 'No application selected.',
            ], 422);
        }

        $contractDate = Carbon::parse($validated['contract_date']);
        $endAt = ! empty($validated['end_at']) ? Carbon::parse($validated['end_at']) : null;
        $scheduleGroupId = (string) Str::uuid();
        $batchTitle = $validated['batch_title'] ?? $validated['batch_name'] ?? null;
        $notifyBeneficiaries = $request->boolean('notify_beneficiaries', true);

        $scheduledCount = 0;
        $skippedCount = 0;
        $createdContracts = [];

        foreach ($applicationIds as $applicationId) {
            DB::transaction(function () use (
                $applicationId,
                $validated,
                $contractDate,
                $endAt,
                $scheduleGroupId,
                $batchTitle,
                $notifyBeneficiaries,
                &$scheduledCount,
                &$skippedCount,
                &$createdContracts
            ) {
                $application = Application::with(['beneficiary.user', 'jobListing.employer'])->find($applicationId);

                if (! $application) {
                    $skippedCount++;
                    return;
                }

                if (! in_array($application->status, ['assigned', 'job_placement', 'jobplacement'], true)) {
                    $skippedCount++;
                    return;
                }

                $exists = Contract::where('application_id', $application->id)
                    ->whereIn('status', ['scheduled', 'rescheduled'])
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    return;
                }

                $contract = Contract::create([
                    'application_id' => $application->id,
                    'contract_date' => $contractDate,
                    'end_at' => $endAt,
                    'location' => $validated['location'],
                    'notes' => $validated['notes'] ?? null,
                    'schedule_group_id' => $scheduleGroupId,
                    'batch_title' => $batchTitle,
                    'scheduled_by' => auth()->id(),
                    'instructions' => $validated['instructions'] ?? null,
                    'notify_beneficiaries' => $notifyBeneficiaries,
                    'status' => 'scheduled',
                ]);

                $application->update([
                    'status' => 'for_contract',
                ]);

                if ($notifyBeneficiaries && $application->beneficiary?->email) {
                    Mail::to($application->beneficiary->email)->send(
                        new ContractSigningMail($application)
                    );
                }

                $scheduledCount++;
                $createdContracts[] = $this->formatContract(
                    $contract->fresh(['application.beneficiary', 'scheduledBy:id,name,email'])
                );
            });
        }

        if ($scheduledCount === 0) {
            return response()->json([
                'message' => 'No contracts were scheduled. Selected applications may already have scheduled contracts.',
                'scheduled' => $scheduledCount,
                'skipped' => $skippedCount,
                'contracts' => $createdContracts,
            ], 422);
        }

        return response()->json([
            'message' => $scheduledCount > 1
                ? 'Batch contract signing scheduled successfully.'
                : 'Contract scheduled successfully',
            'schedule_group_id' => $scheduleGroupId,
            'batch_title' => $batchTitle,
            'scheduled' => $scheduledCount,
            'skipped' => $skippedCount,
            'contracts' => $createdContracts,
        ]);
    }

    public function reschedule(Request $request, Contract $contract)
    {
        if (! $this->currentUserCanManageSchedules()) {
            abort(403, 'Only Admin or CPESO Admin users can reschedule contract signing.');
        }

        $validated = $request->validate([
            'contract_date' => 'nullable|date',
            'scheduled_at' => 'nullable|date',
            'start' => 'nullable|date',
            'end_at' => 'nullable|date',
            'location' => 'required|string|max:255',
            'reschedule_reason' => 'required|string',
            'instructions' => 'nullable|string',
            'notify_beneficiaries' => 'nullable|boolean',
            'reschedule_scope' => 'nullable|in:single,batch',
        ]);

        $scheduleValue = $validated['contract_date']
            ?? $validated['scheduled_at']
            ?? $validated['start']
            ?? null;

        if (! $scheduleValue) {
            return response()->json([
                'message' => 'Contract date is required for rescheduling.',
            ], 422);
        }

        $targets = $this->resolveRescheduleTargets($contract, $validated['reschedule_scope'] ?? 'single');
        $notifyBeneficiaries = $request->boolean('notify_beneficiaries', true);
        $updated = collect();

        foreach ($targets as $target) {
            $target->update([
                'contract_date' => Carbon::parse($scheduleValue),
                'end_at' => ! empty($validated['end_at']) ? Carbon::parse($validated['end_at']) : $target->end_at,
                'location' => $validated['location'],
                'original_schedule_at' => $target->original_schedule_at ?? $target->contract_date,
                'rescheduled_at' => now(),
                'reschedule_reason' => $validated['reschedule_reason'],
                'instructions' => array_key_exists('instructions', $validated)
                    ? $validated['instructions']
                    : $target->instructions,
                'notify_beneficiaries' => $notifyBeneficiaries,
                'status' => 'rescheduled',
            ]);

            // TODO: Add dedicated contract reschedule notification content.
            $updated->push($target->fresh(['application.beneficiary', 'scheduledBy:id,name,email']));
        }

        return response()->json([
            'message' => $updated->count() > 1
                ? 'Contract signing batch rescheduled successfully.'
                : 'Contract signing rescheduled successfully.',
            'contracts' => $updated->map(fn (Contract $item) => $this->formatContract($item))->values(),
        ]);
    }

    public function upcomingContracts()
    {
        if (! $this->currentUserCanManageSchedules()) {
            return response()->json([]);
        }

        $contracts = Contract::with(['application.beneficiary', 'scheduledBy:id,name,email'])
            ->where(function ($query) {
                $query->where('contract_date', '>=', Carbon::now())
                    ->orWhereHas('application', function ($applicationQuery) {
                        $applicationQuery->where('status', 'contract_signed');
                    });
            })
            ->orderBy('contract_date', 'asc')
            ->get()
            ->map(fn (Contract $contract) => $this->formatContract($contract));

        return response()->json($contracts);
    }

    private function resolveRescheduleTargets(Contract $contract, string $scope)
    {
        if ($scope === 'batch' && $contract->schedule_group_id) {
            return Contract::with(['application.beneficiary'])
                ->where('schedule_group_id', $contract->schedule_group_id)
                ->get();
        }

        return collect([$contract->load(['application.beneficiary'])]);
    }

    private function formatContract(Contract $contract): array
    {
        return [
            'id' => $contract->id,
            'application_id' => $contract->application_id,
            'application_status' => $contract->application?->status,
            'beneficiary_name' => $contract->application
                ? trim(
                    ($contract->application->beneficiary->first_name ?? '') . ' ' .
                    ($contract->application->beneficiary->last_name ?? '')
                ) ?: 'N/A'
                : 'N/A',
            'contract_date' => $contract->contract_date,
            'end_at' => $contract->end_at,
            'location' => $contract->location ?? 'TBA',
            'status' => $contract->status,
            'result' => $contract->result,
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
        ];
    }

    private function currentUserCanManageSchedules(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole(['Admin', 'Super Admin', 'PESO Admin']) ?? false;
    }
}
