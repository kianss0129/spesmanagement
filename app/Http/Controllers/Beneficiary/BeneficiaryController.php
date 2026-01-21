<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Models\EmployerRating;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    // =========================
    // UPLOAD DOCUMENTS (UPDATED)
    // =========================
    public function uploadDocs(Request $request)
    {
        $user = auth()->user();

        $beneficiary = $user->beneficiary
            ?? Beneficiary::where('email', $user->email)->first();

        if (!$beneficiary) {
            return response()->json(['message' => 'Beneficiary profile not found'], 404);
        }

        // Validate dynamic document fields
        $request->validate([
            'documents' => 'sometimes|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',

            // Optional named fields
            'birth_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'school_record' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'osy_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'income_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'displacement_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'termination_notice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $stored = [];

        $folder = "documents/beneficiaries/{$user->id}";

        // Handle documents[]
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $stored[] = $file->store($folder, 'public');
            }
        }

        // Handle named fields
        foreach ([
            'birth_certificate',
            'school_record',
            'osy_certificate',
            'income_proof',
            'displacement_certificate',
            'termination_notice',
        ] as $field) {

            if ($request->hasFile($field)) {
                $stored[$field] = $request->file($field)->store($folder, 'public');
            }
        }

        if (empty($stored)) {
            return response()->json(['message' => 'No documents uploaded'], 400);
        }

        // Merge with existing
        $existing = $beneficiary->documents ?? [];

        if (is_string($existing)) {
            $existing = json_decode($existing, true) ?: [];
        }

        $merged = array_merge((array) $existing, (array) $stored);

        $beneficiary->update([
            'documents' => $merged,
            'is_approved' => false // reset approval on new upload
        ]);

        return response()->json([
            'message' => 'Documents uploaded successfully',
            'files' => $stored
        ]);
    }

    // =========================

    public function listApplications()
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $applications = Application::where('beneficiary_id', $user->id)
            ->with('jobListing')
            ->get();

        return response()->json(['applications' => $applications]);
    }

    public function interviews()
    {
        $userId = auth()->id();

        $interviews = Interview::where('beneficiary_id', $userId)
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

    public function jobs()
    {
        return \App\Models\JobListing::with('employer')->get();
    }

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

    public function getRatings($id = null)
    {
        $user = $id ? \App\Models\User::find($id) : auth()->user();

        if (! $user) {
            return response()->json(['ratings' => [], 'average' => 0]);
        }

        $beneficiary = $user->beneficiary
            ?? Beneficiary::where('email', $user->email)->first();

        if (! $beneficiary) {
            return response()->json(['ratings' => [], 'average' => 0]);
        }

        $ratings = $beneficiary->ratings()->with(['employer','application'])->get();
        $avg = $ratings->avg('overall') ?: 0;

        return response()->json([
            'ratings' => $ratings,
            'average' => round($avg, 2)
        ]);
    }

    public function workHistory($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);

        return response()->json([
            'beneficiary' => $beneficiary->name,
            'timeline' => []
        ]);
    }

    public function myWorkHistory()
    {
        $user = auth()->user();
        $beneficiary = $user?->beneficiary;

        if (! $beneficiary) return response()->json(['timeline' => []]);

        return $this->workHistory($beneficiary->id);
    }

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

    public function profilePage()
    {
        $user = auth()->user();
        if (!$user) abort(403);

        $beneficiary = $user->beneficiary
            ?? Beneficiary::where('email', $user->email)->first();

        $ratings = $beneficiary?->ratings ?? collect();
        $average = round($ratings->avg('overall') ?? 0, 1);

        return Inertia::render('Beneficiary/Profile', [
            'beneficiary' => $beneficiary,
            'ratings' => $ratings,
            'average' => $average,
        ]);
    }
}
