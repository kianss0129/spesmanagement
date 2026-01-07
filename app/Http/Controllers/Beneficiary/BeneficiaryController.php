<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Models\EmployerRating;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class BeneficiaryController extends Controller
{
    // Dashboard page
    public function dashboard()
    {
        return Inertia::render('Beneficiary/Dashboard');
    }

    // Apply to Job
    public function apply(Request $request)
    {
        $data = $request->validate([
            'job_listing_id' => 'required'
        ]);

        $data['beneficiary_id'] = auth()->user()->id;

        Application::create($data);

        return response()->json(['message' => 'Application submitted']);
    }

    // Upload Documents
    public function uploadDocs(Request $request)
    {
        $request->validate([
            'documents' => 'sometimes|array',
            'documents.*' => 'file|mimes:pdf,jpg,png|max:5120'
        ]);

        if (! $request->hasFile('documents')) {
            return response()->json(['message' => 'No documents uploaded'], 400);
        }

        $paths = [];
        foreach ($request->file('documents') as $file) {
            $paths[] = $file->store('documents', 'public');
        }

        $user = auth()->user();
        $beneficiary = null;
        if (method_exists($user, 'beneficiary') && $user->beneficiary) {
            $beneficiary = $user->beneficiary;
        } else {
            // Fallback to matching by email in case the relation is not defined on User
            $beneficiary = \App\Models\Beneficiary::where('email', $user->email)->first();
        }

        if (! $beneficiary) {
            // Don't fail the upload if beneficiary profile is missing; files were stored.
            Log::warning('upload-documents: beneficiary profile not found for user', ['user_id' => auth()->id()]);
            return response()->json([
                'message' => 'Documents uploaded (no beneficiary profile found to attach)',
                'paths' => $paths
            ], 200);
        }

        $existing = $beneficiary->documents ?? [];
        if (is_string($existing)) {
            $existing = json_decode($existing, true) ?: [];
        }

        $all = array_values(array_merge((array) $existing, $paths));
        $beneficiary->update(['documents' => $all]);

        return response()->json(['message' => 'Documents uploaded', 'paths' => $paths]);
    }

    // List applications for authenticated beneficiary
    public function listApplications()
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        try {
            if (method_exists($user, 'applications')) {
                $applications = $user->applications()->with(['jobListing'])->get();
            } else {
                $applications = Application::where('beneficiary_id', $user->id)->get();
            }

            return response()->json(['applications' => $applications]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to load applications', 'error' => $e->getMessage()], 500);
        }
    }

    // Upcoming Interviews
  public function interviews()
{
    $userId = auth()->id();

    $interviews = \App\Models\Interview::where('beneficiary_id', $userId)
        ->where('scheduled_at', '>=', now())
        ->with(['jobListing.employer:id,name'])
        ->orderBy('scheduled_at')
        ->get()
        ->map(function($interview) {
            return [
                'id' => $interview->id,
                'job_title' => $interview->jobListing->title ?? 'Job',
                'employer_name' => $interview->jobListing->employer->name ?? 'Employer',
                'scheduled_at' => $interview->scheduled_at,
                'meet_link' => $interview->meet_link,
            ];
        });

    return response()->json($interviews);
}


    // Return available job listings for beneficiaries
    public function jobs()
    {
        return \App\Models\JobListing::with('employer')->get();
    }

    // Submit attendance/report (simple implementation)
    public function submitAttendance(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'status' => 'required|string',
            'remarks' => 'nullable|string'
        ]);

        $attendance = \App\Models\Attendance::create(array_merge($data, [
            'beneficiary_id' => auth()->id()
        ]));

        return response()->json(['message' => 'Attendance submitted', 'attendance' => $attendance]);
    }

    // Ratings
    public function ratings()
    {
        return EmployerRating::where('beneficiary_id', auth()->id())->get();
    }

    public function getRatings($id = null)
    {
        // Resolve the beneficiary model. The application stores ratings on the `Beneficiary` model,
        // while the authenticated user is an instance of `User`. Try to resolve accordingly.
        if ($id) {
            $user = \App\Models\User::find($id);
        } else {
            $user = auth()->user();
        }

        if (! $user) {
            return response()->json(['ratings' => [], 'average' => 0]);
        }

        // Try direct relation `beneficiary` on User, then fallback to matching by email.
        $beneficiaryModel = null;
        if (method_exists($user, 'beneficiary') && $user->beneficiary) {
            $beneficiaryModel = $user->beneficiary;
        } else {
            $beneficiaryModel = \App\Models\Beneficiary::where('email', $user->email)->first();
        }

        if (! $beneficiaryModel) {
            return response()->json(['ratings' => [], 'average' => 0]);
        }

        $ratings = $beneficiaryModel->ratings()->with(['employer','application'])->get();
        $avg = $ratings->avg('overall') ?: 0;

        return response()->json([
            'ratings' => $ratings,
            'average' => round($avg, 2)
        ]);
    }

    // Consolidated work history for a beneficiary (timeline)
    public function workHistory($id)
    {
        $beneficiary = \App\Models\Beneficiary::findOrFail($id);

        $ratings = \App\Models\EmployerRating::where('beneficiary_id', $id)
            ->with('employer','application')
            ->get()
            ->map(function($r){
                return [
                    'type' => 'rating',
                    'date' => $r->created_at->toDateTimeString(),
                    'data' => [
                        'employer' => optional($r->employer)->name,
                        'scores' => [
                            'punctuality' => $r->punctuality,
                            'work_attitude' => $r->work_attitude,
                            'output_quality' => $r->output_quality,
                            'communication' => $r->communication,
                            'overall' => $r->overall,
                        ],
                        'comment' => $r->comment
                    ]
                ];
            });

        $workOutputs = \App\Models\WorkOutput::where('beneficiary_id', $id)
            ->get()
            ->map(function($w){
                return [
                    'type' => 'work_output',
                    'date' => $w->created_at->toDateTimeString(),
                    'data' => [
                        'employer_id' => $w->employer_id,
                        'file_path' => $w->file_path,
                        'original_name' => $w->original_name
                    ]
                ];
            });

        $interviews = \App\Models\Interview::where('beneficiary_id', $id)
            ->get()
            ->map(function($i){
                return [
                    'type' => 'interview',
                    'date' => optional($i->scheduled_at)->toDateTimeString(),
                    'data' => [
                        'job_id' => $i->job_id ?? $i->job_listing_id ?? null,
                        'employer_id' => $i->employer_id,
                        'meet_link' => $i->meet_link,
                        'status' => $i->status
                    ]
                ];
            });

        $applications = \App\Models\Application::where('beneficiary_id', $id)
            ->with('jobListing')
            ->get()
            ->map(function($a){
                return [
                    'type' => 'application',
                    'date' => $a->created_at->toDateTimeString(),
                    'data' => [
                        'job_listing' => optional($a->jobListing)->title,
                        'status' => $a->status
                    ]
                ];
            });

        // Merge and sort by date descending
        $timeline = collect()->merge($ratings)->merge($workOutputs)->merge($interviews)->merge($applications)
            ->sortByDesc(function($i){ return $i['date']; })
            ->values();

        return response()->json(['beneficiary' => $beneficiary->name, 'timeline' => $timeline]);
    }

    // Self work history
    public function myWorkHistory()
    {
        $user = auth()->user();
        $beneficiary = $user ? $user->beneficiary : null;
        if (! $beneficiary) return response()->json(['timeline' => []]);
        return $this->workHistory($beneficiary->id);
    }
    // Attendance analytics (dummy example, replace with real query)
    public function attendance()
    {
        $data = [];
        for ($i = 0; $i < 30; $i++) {
            $data[] = [
                'date' => now()->subDays($i)->format('Y-m-d'),
                'percentage' => rand(70, 100),
            ];
        }
        return response()->json(array_reverse($data));
    }

    // Render profile page with beneficiary data
 public function profilePage()
{
    $user = auth()->user();
    if (!$user) abort(403);

    // Try to get beneficiary
    $beneficiary = $user->beneficiary
        ?? \App\Models\Beneficiary::where('email', $user->email)->first();

    if ($beneficiary) {
        $beneficiary->load(['school','pesoOffice','workHistory','ratings.employer','ratings.application.jobListing']);
        $ratings = $beneficiary->ratings ?? collect();
        $average = round($ratings->avg('overall') ?? 0, 1);
    } else {
        $ratings = collect();
        $average = 0;
    }

    return Inertia::render('Beneficiary/Profile', [
        'beneficiary' => $beneficiary,
        'ratings' => $ratings,
        'average' => $average,
    ]);
}
}