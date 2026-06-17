<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\JobListing;
use App\Models\Skill;
use App\Models\Employer;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Support\Facades\DB;

class AdminAssignmentController extends Controller
{
    /**
     * Get all approved beneficiaries for admin dashboard
     */
    public function getBeneficiaries(Request $request)
    {
        try {
            $query = Beneficiary::with('user', 'skills')
                ->where('approved', true)
                ->whereNull('employer_id')
                ->where(function ($query) {
                    $query->whereNull('employment_status')
                        ->orWhereNotIn('employment_status', ['employed', 'assigned', 'completed']);
                });

            // Filter by employment status
            if ($request->has('employment_status') && $request->input('employment_status')) {
                $query->where('employment_status', $request->input('employment_status'));
            }

            // Filter by location
            if ($request->has('location') && $request->input('location')) {
                $query->where('location', 'like', '%' . $request->input('location') . '%');
            }

            // Filter by beneficiary category
            if ($request->filled('category')) {
                $category = $request->input('category');
                $aliases = array_values(array_unique([
                    $category,
                    str_replace('_', ' ', $category),
                    $category === 'out_of_school_youth' ? 'osy' : $category,
                    $category === 'dependent_of_displaced_worker' ? 'dependent' : $category,
                ]));

                $query->where(function ($categoryQuery) use ($aliases) {
                    $categoryQuery->whereIn('category', $aliases)
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->whereIn('beneficiary_type', $aliases));
                });
            }

            // Search by name or email
            if ($request->has('search') && $request->input('search')) {
                $search = $request->input('search');
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            // Exclude already assigned
            if ($request->has('exclude_assigned') && $request->input('exclude_assigned')) {
                $query->whereNull('job_id');
            }

            $beneficiaries = $query->paginate(15);

            return response()->json($beneficiaries, 200);
        } catch (Throwable $e) {
            return response()->json([], 200);
        }
    }

    /**
     * Get single beneficiary profile
     */
    public function getBeneficiaryProfile($id)
    {
        try {
            $beneficiary = Beneficiary::with('user', 'skills', 'employer', 'job')
                ->findOrFail($id);

            return response()->json($beneficiary, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Beneficiary not found'], 404);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 200);
        }
    }

    /**
     * Get all jobs with slots available
     */
    public function getAllJobs(Request $request)
    {
        try {
            $jobs = JobListing::with(['employer', 'skills.skillCategory', 'applications'])
                ->where('slots', '>', 0)
                ->where(function ($query) {
                    $query->whereNull('closing_date')
                        ->orWhereDate('closing_date', '>=', now()->toDateString());
                })
                ->latest()
                ->get()
                ->filter(fn ($job) => $this->availableSlots($job) > 0)
                ->values()
                ->map(fn ($job) => $this->formatJobForSelection($job));

            return response()->json($jobs, 200);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function formatJobForSelection(JobListing $job): array
    {
        return [
            'id' => $job->id,
            'title' => $job->title,
            'description' => $job->description,
            'location' => $job->location,
            'type' => $job->type,
            'slots' => $this->availableSlots($job),
            'total_slots' => $job->slots,
            'available_slots' => $this->availableSlots($job),
            'closing_date' => $job->closing_date,
            'employer' => $job->employer,
            'employer_name' => $job->employer?->company_name,
            'skills' => $job->skills
                ->map(fn ($skill) => [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'category' => $skill->skillCategory?->name ?? $skill->category ?? 'Skill',
                ])
                ->values(),
        ];
    }

    /**
     * Get matched jobs for PESO admin dashboard - shows match scores for all jobs
     */
    public function getAdminMatchedJobs()
    {
        try {
            // Get current user and their beneficiary profile
            $user = auth()->user();
            $beneficiary = $user->beneficiary;

            // If user is admin/staff without beneficiary profile, return all jobs without matching
            if (!$beneficiary) {
                $jobs = JobListing::with('employer', 'skills')
                    ->where('slots', '>', 0)
                    ->where(function ($query) {
                        $query->whereNull('closing_date')
                            ->orWhereDate('closing_date', '>=', now()->toDateString());
                    })
                    ->latest()
                    ->get();

                return response()->json($jobs, 200);
            }

            // Get beneficiary's skill IDs using the relationship
            $userSkillIds = $beneficiary->skills->pluck('id')->toArray();

            // Get all available jobs
            $jobs = JobListing::with('employer', 'skills')
                ->where('slots', '>', 0)
                ->where(function ($query) {
                    $query->whereNull('closing_date')
                        ->orWhereDate('closing_date', '>=', now()->toDateString());
                })
                ->latest()
                ->get();

            // Map each job with match score
            $jobs = $jobs->map(function ($job) use ($userSkillIds) {
                $jobSkillIds = $job->skills->pluck('id')->toArray();

                $matched = array_intersect($userSkillIds, $jobSkillIds);
                $job->match_score = count($jobSkillIds)
                    ? round((count($matched) / count($jobSkillIds)) * 100)
                    : 0;

                $job->match_level = $job->match_score >= 80 ? 'High' :
                    ($job->match_score >= 50 ? 'Medium' : 'Low');

                return $job;
            });

            $jobs = $jobs->sortByDesc('match_score')->values();
            return response()->json($jobs, 200);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all employers
     */
    public function getAllEmployers(Request $request)
    {
        try {
            $employers = Employer::with([
                    'user',
                    'jobListings' => fn ($query) => $query
                        ->where('slots', '>', 0)
                        ->where(function ($jobQuery) {
                            $jobQuery->whereNull('closing_date')
                                ->orWhereDate('closing_date', '>=', now()->toDateString());
                        })
                        ->with('applications')
                        ->latest(),
                ])
                ->where('approval_status', 'approved')
                ->get()
                ->map(fn ($employer) => [
                    'id' => $employer->id,
                    'company_name' => $employer->company_name,
                    'email' => $employer->email,
                    'contact_person' => $employer->contact_person,
                    'phone' => $employer->phone,
                    'address' => $employer->address,
                    'approval_status' => $employer->approval_status,
                    'available_jobs' => $employer->jobListings
                        ->filter(fn ($job) => $this->availableSlots($job) > 0)
                        ->map(fn ($job) => [
                            'id' => $job->id,
                            'title' => $job->title,
                            'slots' => $this->availableSlots($job),
                            'total_slots' => $job->slots,
                            'available_slots' => $this->availableSlots($job),
                            'closing_date' => $job->closing_date,
                            'employer_id' => $job->employer_id,
                        ])
                        ->values(),
                ]);

            return response()->json($employers, 200);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DEBUG: Check data for matching beneficiaries for a specific job
     */
    public function debugMatchingSuggestions($jobId)
    {
        try {
            $job = JobListing::with('skills')->findOrFail($jobId);

            $jobSkills = $job->skills->pluck('id')->toArray();
            
            // Get all beneficiaries with debug info
            $beneficiaries = Beneficiary::with('user', 'skills')
                ->where('approved', true)
                ->get()
                ->map(function ($b) use ($jobSkills) {
                    $beneficiarySkills = DB::table('beneficiary_skill')
    ->where('beneficiary_id', $b->id)
    ->pluck('skill_id')
    ->toArray();
                    $matched = array_intersect($beneficiarySkills, $jobSkills);
                    
                    return [
                        'id' => $b->id,
                        'name' => $b->user?->name ?? $b->name,
                        'approved' => $b->approved,
                        'beneficiary_skills' => $beneficiarySkills,
                        'job_skills' => $jobSkills,
                        'matched' => $matched,
                        'match_count' => count($matched),
                        'total_job_skills' => count($jobSkills)
                    ];
                })
                ->toArray();

            return response()->json([
                'job_id' => $job->id,
                'job_title' => $job->title,
                'job_skills' => $jobSkills,
                'total_beneficiaries' => count($beneficiaries),
                'beneficiaries' => $beneficiaries
            ], 200);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get matching beneficiaries for a specific job
     */
 public function getMatchingSuggestions($jobId)
{
    $job = JobListing::with(['skills', 'applications'])->findOrFail($jobId);

    if ($this->isJobClosed($job) || $this->availableSlots($job) <= 0) {
        return response()->json([
            'suggestions' => []
        ]);
    }

    $jobSkills = $job->skills->pluck('id')->toArray();

    $beneficiaries = Beneficiary::with('user', 'skills')
        ->where('approved', true)
        ->get()
        ->map(function ($b) use ($jobSkills) {

            $beneficiarySkills = DB::table('beneficiary_skill')
                ->where('beneficiary_id', $b->id)
                ->pluck('skill_id')
                ->toArray();

            $matched = array_intersect($beneficiarySkills, $jobSkills);

            $score = count($jobSkills)
                ? round((count($matched) / count($jobSkills)) * 100)
                : 0;

            return [
                'id' => $b->id,
                'name' => $b->user?->name ?? $b->first_name,
                'email' => $b->email,
                'phone' => $b->phone,
                'location' => $b->city,
                'match_score' => $score,
                'matched' => $matched
            ];
        })
        ->filter(fn ($b) => $b['match_score'] > 0) // IMPORTANT
        ->sortByDesc('match_score')
        ->values();

    return response()->json([
        'suggestions' => $beneficiaries
    ]);
}

    /**
     * Assign beneficiary to job
     * - Creates an Application record
     * - Updates beneficiary with job_id and employer_id
     */
    public function assignBeneficiary(Request $request, $beneficiaryId)
    {
        try {
            $validated = $request->validate([
                'employer_id' => 'required|exists:employers,id',
                'job_listing_id' => 'required_without:job_id|exists:job_listings,id',
                'job_id' => 'required_without:job_listing_id|exists:job_listings,id',
            ]);

            $beneficiary = Beneficiary::findOrFail($beneficiaryId);
            $jobId = $validated['job_listing_id'] ?? $validated['job_id'];

            if (! $beneficiary->approved || $beneficiary->approval_status !== 'approved') {
                return response()->json(['error' => 'Only approved beneficiaries can be assigned.'], 422);
            }

            $hasActiveAssignment = \App\Models\Application::where('beneficiary_id', $beneficiaryId)
                ->whereNotNull('job_listing_id')
                ->whereNotIn('status', ['completed', 'rejected'])
                ->exists();

            if ($hasActiveAssignment) {
                return response()->json(['error' => 'This beneficiary already has an active assignment.'], 422);
            }
            
            // Get the job and extract employer_id from it
            $job = JobListing::with('applications')->findOrFail($jobId);
            $employerId = $job->employer_id;

            // Verify employer exists
            if (!$employerId) {
                return response()->json(['error' => 'Job has no associated employer'], 400);
            }

            if ((int) $validated['employer_id'] !== (int) $employerId) {
                return response()->json([
                    'message' => 'The selected job does not belong to the selected employer.',
                    'errors' => [
                        'job_listing_id' => ['The selected job does not belong to the selected employer.'],
                    ],
                ], 422);
            }

            if ($this->isJobClosed($job) || $this->availableSlots($job) <= 0) {
                return response()->json([
                    'message' => 'The selected job has no available slots or is already closed.',
                    'errors' => [
                        'job_listing_id' => ['The selected job has no available slots or is already closed.'],
                    ],
                ], 422);
            }

            // Update beneficiary with job and employer
            $beneficiary->update([
                'job_id' => $jobId,
                'employer_id' => $employerId,
                'employment_status' => 'assigned',
            ]);

            // Create Application record to link beneficiary to job
            \App\Models\Application::updateOrCreate(
                [
                    'beneficiary_id' => $beneficiaryId,
                    'job_listing_id' => $jobId
                ],
                [
                    'status' => 'assigned'
                ]
            );

            // Reload beneficiary to get updated data
            $beneficiary->refresh();

            try {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($beneficiary)
                    ->withProperties([
                        'module' => 'Placement',
                        'user_id' => $beneficiary->user_id,
                        'status' => 'assigned',
                    ])
                    ->log('Beneficiary assigned to employer');
            } catch (Throwable $e) {
                report($e);
            }

            return response()->json([
                'message' => 'Beneficiary assigned successfully',
                'beneficiary' => $beneficiary->load('employer', 'job')
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Resource not found'], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function availableSlots(JobListing $job): int
    {
        if ($this->isJobClosed($job)) {
            return 0;
        }

        $filledApplicationStatuses = [
            'assigned',
            'for_contract',
            'contract_signed',
            'deployed',
            'ongoing',
            'completion_review',
            'completed',
        ];

        $applicationBeneficiaryIds = $job->relationLoaded('applications')
            ? $job->applications
                ->filter(fn ($application) => in_array($application->status, $filledApplicationStatuses, true))
                ->pluck('beneficiary_id')
            : \App\Models\Application::where('job_listing_id', $job->id)
                ->whereIn('status', $filledApplicationStatuses)
                ->pluck('beneficiary_id');

        $directBeneficiaryIds = Beneficiary::where('job_id', $job->id)->pluck('id');
        $filledSlots = $applicationBeneficiaryIds
            ->merge($directBeneficiaryIds)
            ->filter()
            ->unique()
            ->count();

        return max((int) $job->slots - $filledSlots, 0);
    }

    private function isJobClosed(JobListing $job): bool
    {
        return filled($job->closing_date) && $job->closing_date < now()->toDateString();
    }

    /**
     * Get skills for filter dropdown
     */
    public function getSkillsForFilter()
    {
        try {
            $skills = Skill::with('skillCategory')
                ->select('id', 'name', 'category', 'skill_category_id')
                ->orderBy('name')
                ->get()
                ->map(fn ($skill) => [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'category' => $skill->skillCategory?->name ?? $skill->category ?? 'Skill',
                ]);

            return response()->json($skills, 200);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get education levels
     */
    public function getEducationLevels()
    {
        try {
            $levels = DB::table('beneficiaries')
                ->select('education_level')
                ->distinct()
                ->pluck('education_level')
                ->filter()
                ->values();

            return response()->json($levels, 200);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
