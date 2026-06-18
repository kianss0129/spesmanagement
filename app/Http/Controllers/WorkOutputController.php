<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Beneficiary;
use App\Models\WorkOutput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class WorkOutputController extends Controller
{
    public function beneficiaryIndex(Request $request)
    {
        if (! $request->expectsJson()) {
            return Inertia::render('Beneficiary/WorkOutputs');
        }

        $beneficiary = $request->user()->beneficiary;

        if (! $beneficiary) {
            return response()->json([]);
        }

        $reports = WorkOutput::with($this->relations())
            ->where('beneficiary_id', $beneficiary->id)
            ->latest('work_date')
            ->latest()
            ->get()
            ->map(fn (WorkOutput $workOutput) => $this->formatWorkOutput($workOutput));

        return response()->json($reports);
    }

    public function beneficiaryStore(Request $request)
    {
        $beneficiary = $request->user()->beneficiary;

        if (! $beneficiary) {
            return response()->json([
                'message' => 'Beneficiary profile not found.',
            ], 403);
        }

        $assignment = $this->resolveBeneficiaryAssignment($beneficiary);

        if (! $assignment['employer_id']) {
            return response()->json([
                'message' => 'You need an assigned employer before submitting a Daily Accomplishment Report.',
            ], 422);
        }

        $validated = $request->validate([
            'work_date' => 'required|date',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'accomplishments' => 'required|string',
            'hours_worked' => 'required|numeric|min:0|max:24',
            'attachment' => 'nullable|file|max:10240',
            'file' => 'nullable|file|max:10240',
        ]);

        $file = $request->file('attachment') ?: $request->file('file');
        $path = null;
        $originalName = null;

        if ($file) {
            $path = $file->store('work_outputs', 'public');
            $originalName = $file->getClientOriginalName();
        }

        $workOutput = WorkOutput::create([
            'employer_id' => $assignment['employer_id'],
            'beneficiary_id' => $beneficiary->id,
            'application_id' => $assignment['application_id'],
            'job_listing_id' => $assignment['job_listing_id'],
            'work_date' => $validated['work_date'],
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'accomplishments' => $validated['accomplishments'],
            'hours_worked' => $validated['hours_worked'],
            'status' => 'submitted',
            'submitted_by' => $request->user()->id,
            'original_submitted_at' => now(),
            'file_path' => $path,
            'original_name' => $originalName,
        ]);

        // Auto-transition: deployed → ongoing on first work report submission
        if ($assignment['application_id']) {
            $activeApplication = Application::find($assignment['application_id']);
            if ($activeApplication && $activeApplication->status === 'deployed') {
                $activeApplication->update(['status' => 'ongoing']);
            }
        }

        try {
            activity()
                ->causedBy($request->user())
                ->performedOn($workOutput)
                ->withProperties([
                    'module' => 'Work Output',
                    'user_id' => $request->user()->id,
                    'status' => 'submitted',
                ])
                ->log('Beneficiary submitted daily accomplishment report');
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Daily Accomplishment Report submitted successfully.',
            'report' => $this->formatWorkOutput($workOutput->fresh($this->relations())),
        ], 201);
    }

    public function beneficiaryResubmit(Request $request, WorkOutput $workOutput)
    {
        $beneficiary = $request->user()->beneficiary;

        if (! $beneficiary || (int) $workOutput->beneficiary_id !== (int) $beneficiary->id) {
            abort(403, 'You can only resubmit your own daily reports.');
        }

        if ($workOutput->status !== 'needs_correction') {
            return response()->json([
                'message' => 'Only reports marked as Needs Correction can be resubmitted.',
            ], 422);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'accomplishments' => 'required|string',
            'hours_worked' => 'required|numeric|min:0|max:24',
            'attachment' => 'nullable|file|max:10240',
            'file' => 'nullable|file|max:10240',
        ]);

        $file = $request->file('attachment') ?: $request->file('file');
        $updates = [
            'title' => $validated['title'] ?? null,
            'accomplishments' => $validated['accomplishments'],
            'hours_worked' => $validated['hours_worked'],
            'status' => 'submitted',
            'submitted_by' => $request->user()->id,
            'original_submitted_at' => $workOutput->original_submitted_at ?: $workOutput->created_at,
            'resubmitted_at' => now(),
        ];

        if ($file) {
            if ($workOutput->file_path) {
                Storage::disk('public')->delete($workOutput->file_path);
            }

            $updates['file_path'] = $file->store('work_outputs', 'public');
            $updates['original_name'] = $file->getClientOriginalName();
        }

        $workOutput->update($updates);

        try {
            activity()
                ->causedBy($request->user())
                ->performedOn($workOutput)
                ->withProperties([
                    'module' => 'Work Output',
                    'user_id' => $request->user()->id,
                    'status' => 'resubmitted',
                    'original_submitted_at' => optional($workOutput->original_submitted_at ?: $workOutput->created_at)->toDateTimeString(),
                    'resubmitted_at' => optional($workOutput->resubmitted_at)->toDateTimeString(),
                ])
                ->log('Beneficiary resubmitted daily accomplishment report');
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Daily Accomplishment Report resubmitted successfully.',
            'report' => $this->formatWorkOutput($workOutput->fresh($this->relations())),
        ]);
    }

    public function employerIndex(Request $request)
    {
        if (! $request->expectsJson()) {
            return Inertia::render('Employer/WorkOutput');
        }

        $employer = $request->user()->employer;

        if (! $employer) {
            return response()->json([]);
        }

        $reports = WorkOutput::with($this->relations())
            ->where('employer_id', $employer->id)
            ->latest('work_date')
            ->latest()
            ->get()
            ->map(fn (WorkOutput $workOutput) => $this->formatWorkOutput($workOutput));

        return response()->json($reports);
    }

    public function approve(Request $request, WorkOutput $workOutput)
    {
        $this->authorizeEmployerReview($request, $workOutput);

        $validated = $request->validate([
            'review_remarks' => 'nullable|string',
        ]);

        $workOutput->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_remarks' => $validated['review_remarks'] ?? null,
        ]);

        try {
            activity()
                ->causedBy($request->user())
                ->performedOn($workOutput)
                ->withProperties([
                    'module' => 'Work Output',
                    'user_id' => $request->user()->id,
                    'status' => 'approved',
                ])
                ->log('Work output approved');
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Daily report approved.',
            'report' => $this->formatWorkOutput($workOutput->fresh($this->relations())),
        ]);
    }

    public function reject(Request $request, WorkOutput $workOutput)
    {
        $this->authorizeEmployerReview($request, $workOutput);

        $validated = $request->validate([
            'review_remarks' => 'required|string',
            'status' => 'nullable|in:rejected,needs_correction',
        ]);

        $workOutput->update([
            'status' => $validated['status'] ?? 'needs_correction',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_remarks' => $validated['review_remarks'],
        ]);

        try {
            activity()
                ->causedBy($request->user())
                ->performedOn($workOutput)
                ->withProperties([
                    'module' => 'Work Output',
                    'user_id' => $request->user()->id,
                    'status' => $validated['status'] ?? 'needs_correction',
                ])
                ->log('Work output returned for correction');
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Daily report returned for correction.',
            'report' => $this->formatWorkOutput($workOutput->fresh($this->relations())),
        ]);
    }

    public function pesoIndex(Request $request)
    {
        $query = WorkOutput::with($this->relations());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employer')) {
            $keyword = $request->employer;
            $query->whereHas('employer', function ($employerQuery) use ($keyword) {
                $employerQuery->where('company_name', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('beneficiary')) {
            $keyword = $request->beneficiary;
            $query->whereHas('beneficiary', function ($beneficiaryQuery) use ($keyword) {
                $beneficiaryQuery
                    ->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('work_date', $request->date);
        }

        return response()->json(
            $query->latest('work_date')
                ->latest()
                ->get()
                ->map(fn (WorkOutput $workOutput) => $this->formatWorkOutput($workOutput))
        );
    }

    private function authorizeEmployerReview(Request $request, WorkOutput $workOutput): void
    {
        $employer = $request->user()->employer;

        if (! $employer || ! $this->employerCanAccess($employer->id, $workOutput)) {
            abort(403, 'You can only review reports from beneficiaries assigned to your employer account.');
        }
    }

    private function employerCanAccess(int $employerId, WorkOutput $workOutput): bool
    {
        if ((int) $workOutput->employer_id === $employerId) {
            return true;
        }

        if ((int) optional($workOutput->beneficiary)->employer_id === $employerId) {
            return true;
        }

        return (int) optional(optional($workOutput->application)->jobListing)->employer_id === $employerId;
    }

    private function resolveBeneficiaryAssignment(Beneficiary $beneficiary): array
    {
        $application = Application::with('jobListing.employer')
            ->where('beneficiary_id', $beneficiary->id)
            ->where(function ($query) use ($beneficiary) {
                $query->whereIn('status', ['assigned', 'contract_signed', 'deployed', 'ongoing', 'completion_review'])
                    ->orWhereHas('jobListing', fn ($jobQuery) => $jobQuery->where('assigned_beneficiary_id', $beneficiary->id));
            })
            ->latest()
            ->first();

        $jobListing = $application?->jobListing;

        return [
            'application_id' => $application?->id,
            'job_listing_id' => $jobListing?->id ?? $beneficiary->job_id,
            'employer_id' => $beneficiary->employer_id ?? $jobListing?->employer_id,
        ];
    }

    private function relations(): array
    {
        return [
            'beneficiary.user',
            'employer',
            'application.jobListing.employer',
            'jobListing',
            'submittedBy:id,name,email',
            'reviewedBy:id,name,email',
        ];
    }

    private function formatWorkOutput(WorkOutput $workOutput): array
    {
        $beneficiary = $workOutput->beneficiary;
        $beneficiaryName = trim(implode(' ', array_filter([
            $beneficiary?->first_name,
            $beneficiary?->middle_name,
            $beneficiary?->last_name,
            $beneficiary?->suffix,
        ])));

        $employer = $workOutput->employer ?? $workOutput->application?->jobListing?->employer;
        $fileUrl = $workOutput->file_path ? Storage::url($workOutput->file_path) : null;

        return [
            'id' => $workOutput->id,
            'application_id' => $workOutput->application_id,
            'job_listing_id' => $workOutput->job_listing_id,
            'beneficiary_id' => $workOutput->beneficiary_id,
            'beneficiary_name' => $beneficiaryName ?: $beneficiary?->user?->name ?: 'Unknown beneficiary',
            'employer_id' => $employer?->id,
            'employer_name' => $employer?->company_name ?? 'Unknown employer',
            'job_title' => $workOutput->jobListing?->title ?? $workOutput->application?->jobListing?->title,
            'work_date' => optional($workOutput->work_date)->toDateString(),
            'title' => $workOutput->title,
            'description' => $workOutput->description,
            'accomplishments' => $workOutput->accomplishments,
            'hours_worked' => $workOutput->hours_worked,
            'status' => $workOutput->status,
            'submitted_by' => $workOutput->submittedBy,
            'original_submitted_at' => $workOutput->original_submitted_at ?: $workOutput->created_at,
            'resubmitted_at' => $workOutput->resubmitted_at,
            'reviewed_by' => $workOutput->reviewedBy,
            'reviewed_at' => $workOutput->reviewed_at,
            'review_remarks' => $workOutput->review_remarks,
            'file_path' => $workOutput->file_path,
            'file_url' => $fileUrl,
            'original_name' => $workOutput->original_name,
            'created_at' => $workOutput->created_at,
            'updated_at' => $workOutput->updated_at,
        ];
    }
}
