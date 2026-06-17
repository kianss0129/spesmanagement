<?php


namespace App\Http\Controllers\Employer;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;


class JobController extends Controller
{
   
    // List jobs of logged-in employer
    public function index()
{
    $employer = auth()->user()->employer;


    $jobs = $employer->jobListings()->latest()->get();


    return Inertia::render('Employer/JobList', [
        'jobs' => $jobs
    ]);
}


    // Show create form
    public function create()
    {
        return Inertia::render('Employer/PostJob');
    }


    // Store new job
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'location' => 'required|string',
            'type' => 'required|string',
            'slots' => 'required|integer|min:1',
            'closing_date' => ['required', 'date', 'after_or_equal:today'],
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
            'new_skills' => 'nullable|array',
            'new_skills.*.name' => 'required_with:new_skills|string|max:255',
            'new_skills.*.category' => 'required_with:new_skills|string|max:255',
        ]);


       $employer = auth()->user()->employer;

       unset($validated['skills'], $validated['new_skills']);

       DB::transaction(function () use ($request, $employer, $validated, &$job) {
           $job = $employer->jobListings()->create($validated);
           $skills = $this->resolveRequiredSkillIds($request);
           $this->syncJobSkillsOrFail($job, $skills);
       });

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

        return redirect()->route('employer.jobs.index')->with('success', 'Job posted successfully!');
    }


    // Edit job
    public function edit($id)
{
    $employer = auth()->user()->employer;


    $job = $employer->jobListings()->with('skills')->findOrFail($id);


    return Inertia::render('Employer/EditJob', [
        'job' => $job
    ]);
}


    // Update job
    public function update(Request $request, $id)
{
    $employer = auth()->user()->employer;


    $job = $employer->jobListings()->findOrFail($id);


    $validated = $request->validate([
        'title' => 'required|string',
        'description' => 'nullable|string',
        'location' => 'required|string',
        'type' => 'required|string',
        'slots' => 'required|integer|min:1',
        'closing_date' => ['required', 'date', 'after_or_equal:today'],
        'skills' => 'nullable|array',
        'skills.*' => 'integer|exists:skills,id',
        'new_skills' => 'nullable|array',
        'new_skills.*.name' => 'required_with:new_skills|string|max:255',
        'new_skills.*.category' => 'required_with:new_skills|string|max:255',
    ]);

    unset($validated['skills'], $validated['new_skills']);

    DB::transaction(function () use ($request, $job, $validated) {
        $job->update($validated);
        $skills = $this->resolveRequiredSkillIds($request);
        $this->syncJobSkillsOrFail($job, $skills);
    });


    return redirect()
        ->route('employer.jobs.index')
        ->with('success', 'Job updated successfully!');
}


    // Delete job
   public function destroy($id)
{
    $employer = auth()->user()->employer;


    $job = $employer->jobListings()->findOrFail($id);


    $job->delete();


    return redirect()
        ->route('employer.jobs.index')
        ->with('success', 'Job deleted successfully!');
}


public function show(JobListing $job)
{
    $employer = auth()->user()->employer;

    if (! $employer || (int) $job->employer_id !== (int) $employer->id) {
        abort(403, 'You can only view applicants for your own jobs.');
    }

    return Inertia::render('Employer/Applicants', [
        'jobId' => $job->id
    ]);
}

public function matchedJobs()
{
    $userId = auth()->id();
    $user = auth()->user();

    // 1. Get the beneficiary associated with this user
    $beneficiary = $user->beneficiary;
    if (!$beneficiary) {
        return response()->json([]);
    }

    // 2. Get user skill IDs using the proper relationship
    $userSkillIds = $beneficiary->skills->pluck('id')->toArray();

    // 3. Get employer
    $employer = $user->employer;
    if (!$employer) {
        return response()->json([]);
    }

    $jobs = $employer->jobListings()->latest()->get();

    // 4. Compute match score
    $jobs = $jobs->map(function ($job) use ($userSkillIds) {

        $jobSkillIds = $job->skills->pluck('id')->toArray();

        $matched = array_intersect($userSkillIds, $jobSkillIds);

        $job->match_score = count($jobSkillIds)
            ? round((count($matched) / count($jobSkillIds)) * 100)
            : 0;

        $job->match_level =
            $job->match_score >= 80 ? 'High' :
            ($job->match_score >= 50 ? 'Medium' : 'Low');

        return $job;
    });

    // 5. Sort by best match
    $jobs = $jobs->sortByDesc('match_score')->values();

    return response()->json($jobs);
}

private function validatedSkillIds(array $skills): array
{
    return collect($skills)
        ->map(fn ($skill) => (int) $skill)
        ->filter(fn ($skill) => $skill > 0)
        ->unique()
        ->values()
        ->all();
}

private function resolveRequiredSkillIds(Request $request): array
{
    $existingSkillIds = $this->validatedSkillIds($request->input('skills', []));
    $newSkillIds = collect($request->input('new_skills', []))
        ->map(fn ($skill) => $this->resolveCustomSkill($skill))
        ->filter()
        ->values()
        ->all();

    return collect($existingSkillIds)
        ->merge($newSkillIds)
        ->unique()
        ->values()
        ->all();
}

private function resolveCustomSkill(array $skill): ?int
{
    $name = trim((string) ($skill['name'] ?? ''));
    $categoryName = trim((string) ($skill['category'] ?? ''));

    if ($name === '' || $categoryName === '') {
        return null;
    }

    $category = SkillCategory::whereRaw('LOWER(name) = ?', [mb_strtolower($categoryName)])
        ->first();

    if (! $category) {
        $category = SkillCategory::updateOrCreate(
            ['name' => $categoryName],
            ['description' => 'Employer-defined skill category']
        );
    }

    $existingSkill = Skill::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
        ->where(function ($query) use ($category, $categoryName) {
            $query->where('skill_category_id', $category->id)
                ->orWhere('category', $categoryName);
        })
        ->first();

    if ($existingSkill) {
        return $existingSkill->id;
    }

    $globalSkill = Skill::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

    if ($globalSkill) {
        $globalSkill->fill([
            'category' => $globalSkill->category ?: $categoryName,
            'skill_category_id' => $globalSkill->skill_category_id ?: $category->id,
        ])->save();

        return $globalSkill->id;
    }

    return Skill::updateOrCreate(
        ['name' => $name],
        [
            'category' => $categoryName,
            'skill_category_id' => $category->id,
            'description' => 'Employer-defined required skill',
        ]
    )->id;
}

private function syncJobSkillsOrFail(JobListing $job, array $skills): void
{
    $job->skills()->sync($skills);

    $savedCount = $job->skills()->count();
    if ($savedCount !== count($skills)) {
        throw ValidationException::withMessages([
            'skills' => ['Unable to save one or more selected required skills. Please reload the form and try again.'],
        ]);
    }
}
}
