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
            'job_listing_id' => 'required|exists:job_listings,id'
        ]);

        $data['beneficiary_id'] = auth()->user()->beneficiary->id ?? auth()->id();

        Application::create($data);

        return response()->json(['message' => 'Application submitted']);
    }

    // Upload documents
    public function uploadDocs(Request $request)
    {
        $user = auth()->user();
        $beneficiary = $user->beneficiary ?? Beneficiary::where('email', $user->email)->first();

        if (!$beneficiary) {
            return response()->json(['message' => 'Beneficiary profile not found'], 404);
        }

        // Validate documents
        $request->validate([
            'documents' => 'sometimes|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
            'birth_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'school_record' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'osy_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'income_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'displacement_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'termination_notice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $stored = [];
        $folder = "documents/beneficiaries/{$user->id}";

        // Multiple documents
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $stored[] = $file->store($folder, 'public');
            }
        }

        // Named documents
        foreach (['birth_certificate','school_record','osy_certificate','income_proof','displacement_certificate','termination_notice'] as $field) {
            if ($request->hasFile($field)) {
                $stored[$field] = $request->file($field)->store($folder, 'public');
            }
        }

        if (empty($stored)) {
            return response()->json(['message' => 'No documents uploaded'], 400);
        }

        $existing = $beneficiary->documents ?? [];
        if (is_string($existing)) $existing = json_decode($existing, true) ?: [];
        $merged = array_merge((array) $existing, (array) $stored);

        $beneficiary->update([
            'documents' => $merged,
            'status' => 'pending' // reset approval status
        ]);

        return response()->json([
            'message' => 'Documents uploaded successfully',
            'files' => $stored
        ]);
    }

    // List applications
    public function listApplications()
    {
        $user = auth()->user();
        $applications = Application::where('beneficiary_id', $user->beneficiary->id ?? $user->id)
            ->with('jobListing')
            ->get();

        return response()->json(['applications' => $applications]);
    }

    // Interviews
    public function interviews()
    {
        $userId = auth()->id();
        $interviews = Interview::where('beneficiary_id', $userId)
            ->where('scheduled_at', '>=', now())
            ->with(['jobListing.employer:id,company_name'])
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                'job_title' => $i->jobListing->title ?? 'Job',
                'employer_name' => $i->jobListing->employer->company_name ?? 'Employer',
                'scheduled_at' => $i->scheduled_at,
                'meet_link' => $i->meet_link,
            ]);

        return response()->json($interviews);
    }

    // Jobs list
    public function jobs()
    {
        return \App\Models\JobListing::with('employer')->get();
    }

    // Submit attendance
    public function submitAttendance(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'status' => 'required|string',
            'remarks' => 'nullable|string'
        ]);

        $attendance = \App\Models\Attendance::create(array_merge($data, [
            'beneficiary_id' => auth()->user()->beneficiary->id ?? auth()->id()
        ]));

        return response()->json(['message' => 'Attendance submitted', 'attendance' => $attendance]);
    }

    // Ratings
    public function getRatings($id = null)
    {
        $user = $id ? \App\Models\User::find($id) : auth()->user();
        $beneficiary = $user->beneficiary ?? Beneficiary::where('email', $user->email)->first();

        if (!$beneficiary) return response()->json(['ratings' => [], 'average' => 0]);

        $ratings = $beneficiary->ratings()->with(['employer','application'])->get();
        $avg = $ratings->avg('overall') ?: 0;

        return response()->json(['ratings' => $ratings, 'average' => round($avg,2)]);
    }

    // Work history
    public function workHistory($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);
        return response()->json([
            'beneficiary' => $beneficiary->first_name . ' ' . $beneficiary->last_name,
            'timeline' => $beneficiary->workHistory()->get()
        ]);
    }

    public function myWorkHistory()
    {
        $user = auth()->user();
        $beneficiary = $user?->beneficiary;
        if (!$beneficiary) return response()->json(['timeline'=>[]]);
        return $this->workHistory($beneficiary->id);
    }

    // Attendance overview
    public function attendance()
    {
        $data = [];
        for ($i=0;$i<30;$i++){
            $data[] = ['date'=>now()->subDays($i)->format('Y-m-d'),'percentage'=>rand(70,100)];
        }
        return response()->json(array_reverse($data));
    }

    // Profile page
    public function profilePage()
    {
        $user = auth()->user();
        $beneficiary = $user->beneficiary ?? Beneficiary::where('email', $user->email)->first();
        $ratings = $beneficiary?->ratings ?? collect();
        $average = round($ratings->avg('overall') ?? 0,1);

        return Inertia::render('Beneficiary/Profile',[
            'beneficiary'=>$beneficiary,
            'ratings'=>$ratings,
            'average'=>$average
        ]);
    }

    /**
     * Show verification page for PESO admin to review beneficiary onboarding
     */
    public function showVerification(Beneficiary $beneficiary)
    {
        // Load related data
        $beneficiary->load('user', 'school');

        // Build documents array from stored file paths
        $documents = [];
        if ($beneficiary->documents) {
            $raw = is_array($beneficiary->documents) ? $beneficiary->documents : [$beneficiary->documents];
            foreach ($raw as $entry) {
                $entryPath = null;
                $entryName = null;
                $entryUploadedAt = null;

                if (is_string($entry)) {
                    $entryPath = $entry;
                } elseif (is_array($entry)) {
                    $entryPath = $entry['path'] ?? $entry['file'] ?? null;
                    $entryName = $entry['name'] ?? $entry['filename'] ?? null;
                    $entryUploadedAt = $entry['uploaded_at'] ?? $entry['created_at'] ?? null;
                } elseif (is_object($entry)) {
                    $entryPath = $entry->path ?? $entry->file ?? null;
                    $entryName = $entry->name ?? $entry->filename ?? null;
                    $entryUploadedAt = $entry->uploaded_at ?? $entry->created_at ?? null;
                }

                if (!$entryPath) {
                    continue;
                }

                // Check if file exists on public disk
                $exists = Storage::disk('public')->exists((string) $entryPath);

                // Build URL for storage path
                $url = null;
                if ($exists) {
                    try {
                        $url = Storage::disk('public')->url((string) $entryPath);
                    } catch (\Throwable $e) {
                        $url = null;
                    }
                }

                // Determine uploaded at timestamp
                $uploadedAt = $entryUploadedAt;
                if (!$uploadedAt && $exists) {
                    try {
                        $fullPath = storage_path('app/public/' . ltrim((string) $entryPath, '/'));
                        if (file_exists($fullPath)) {
                            $uploadedAt = date('c', filemtime($fullPath));
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                $documents[] = [
                    'path' => $entryPath,
                    'url' => $url,
                    'name' => $entryName ?? basename((string) $entryPath),
                    'uploaded_at' => $uploadedAt,
                    'exists' => $exists,
                ];
            }
        }

        return view('beneficiaries.verify', [
            'beneficiary' => $beneficiary,
            'documents' => $documents,
        ]);
    }

    /**
     * Handle verification action (approve/reject) for beneficiary
     */
    public function verify(Request $request, Beneficiary $beneficiary)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
        ]);

        if ($validated['action'] === 'approve') {
            $beneficiary->update([
                'approval_status' => 'approved',
                'approved_at' => now(),
                'approved' => true,
                'rejection_reason' => null,
                'rejected_at' => null,
            ]);

            Log::info("Beneficiary {$beneficiary->id} approved by " . auth()->user()->name);

            return redirect()
                ->route('beneficiaries.verify', $beneficiary->id)
                ->with('success', 'Beneficiary has been approved successfully.');
        }

        if ($validated['action'] === 'reject') {
            $beneficiary->update([
                'approval_status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
                'rejected_at' => now(),
                'approved' => false,
                'approved_at' => null,
            ]);

            Log::info("Beneficiary {$beneficiary->id} rejected by " . auth()->user()->name . ". Reason: {$validated['rejection_reason']}");

            return redirect()
                ->route('beneficiaries.verify', $beneficiary->id)
                ->with('success', 'Beneficiary has been rejected. A notification will be sent to the beneficiary.');
        }
    }
}
