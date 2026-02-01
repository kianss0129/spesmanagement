<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Beneficiary;

class OnboardingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        return inertia('Beneficiary/Onboarding', [
            'user'     => $user,
            'category' => $request->query(
                'category',
                $user->beneficiary_type ?? 'student'
            ),
        ]);
    }

    public function step1(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        $firstName = Str::before($validated['name'], ' ');
        $lastName  = Str::contains($validated['name'], ' ')
            ? Str::after($validated['name'], ' ')
            : '-';

        $beneficiary = $user->beneficiary ?? Beneficiary::create([
            'user_id'    => $user->id,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $validated['email'],
            'status'     => 'draft',
        ]);

        $beneficiary->update([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $validated['email'],
            'phone'      => $validated['phone'] ?? $beneficiary->phone ?? null,
        ]);

        return response()->json(['message' => 'Step 1 saved']);
    }

    public function step2(Request $request)
    {
        $beneficiary = Auth::user()->beneficiary;
        abort_if(! $beneficiary, 404);

        $category = $request->input('category');
        $rules = match ($category) {
            'student'   => ['school' => 'required|string|max:255'],
            'osy'       => ['skills' => 'required|string|max:255'],
            'dependent' => ['parentName' => 'required|string|max:255'],
            default     => [],
        };

        $validated = $request->validate($rules);

        if ($category === 'student' && isset($validated['school'])) {
            $schoolName = trim($validated['school']);
            $school = \App\Models\School::firstOrCreate(['name' => $schoolName]);
            $beneficiary->update(['school_id' => $school->id]);
        } else {
            $beneficiary->update($validated);
        }

        return response()->json(['message' => 'Step 2 saved']);
    }

    public function upload(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:1024000',
            'files.*'     => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:1024000',
        ]);

        $uploadedDocs = [];
        $incomingFiles = [];

        if ($request->hasFile('documents')) {
            $incomingFiles = array_merge($incomingFiles, (array) $request->file('documents'));
        }
        if ($request->hasFile('files')) {
            $incomingFiles = array_merge($incomingFiles, (array) $request->file('files'));
        }

        if (!empty($incomingFiles)) {
            $folder = $user->hasRole('Employer')
                ? 'documents/employers'
                : 'documents/users';

            foreach ($incomingFiles as $file) {
                if (! $file) continue;

                // Save to public disk so accessible via /storage/...
                $storedPath = $file->store($folder . '/' . Auth::id(), 'public');

                if ($storedPath) {
                    $uploadedDocs[] = [
                        'path'        => $storedPath,
                        'name'        => $file->getClientOriginalName(),
                        'uploaded_at' => now()->toIso8601String(),
                    ];
                }
            }
        }

        $savedDocuments = [];

        if ($user->hasRole('Employer') || $request->filled('employer_upload')) {
            $employer = $user->employer ?? $user->employer()->create([]);
            $existingDocs = is_array($employer->documents) ? $employer->documents : ($employer->documents ? [$employer->documents] : []);
            $employer->documents = array_merge($existingDocs, $uploadedDocs);
            $employer->save();
            $savedDocuments = $employer->documents ?? [];
        } else {
            $beneficiary = $user->beneficiary;
            abort_if(! $beneficiary, 404);
            $existingDocs = is_array($beneficiary->documents) ? $beneficiary->documents : ($beneficiary->documents ? [$beneficiary->documents] : []);
            $beneficiary->documents = array_merge($existingDocs, $uploadedDocs);
            $beneficiary->save();
            $savedDocuments = $beneficiary->documents ?? [];
        }

        return response()->json([
            'message'   => 'Documents uploaded',
            'documents' => $savedDocuments,
        ]);
    }

    public function submit(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:1024000',
            'files.*'     => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:1024000',
        ]);

        $incomingFiles = [];
        if ($request->hasFile('documents')) {
            $incomingFiles = array_merge($incomingFiles, (array) $request->file('documents'));
        }
        if ($request->hasFile('files')) {
            $incomingFiles = array_merge($incomingFiles, (array) $request->file('files'));
        }

        if ($request->filled('company_name')) {
            $validated = $request->validate([
                'company_name'  => 'required|string|max:255',
                'email'         => 'nullable|email|max:255',
                'contact_person'=> 'nullable|string|max:255',
                'phone'         => 'required|string|max:20',
                'address'       => 'nullable|string|max:255',
            ]);

            if (! $user->hasRole('Employer')) {
                $user->assignRole('Employer');
            }

            $user->update(['name' => $validated['company_name']]);

            $employer = $user->employer ?? $user->employer()->create([]);

            $uploadedDocs = [];
            if (!empty($incomingFiles)) {
                foreach ($incomingFiles as $file) {
                    $storedPath = $file->store('documents/employers/' . Auth::id(), 'public');
                    if ($storedPath) {
                        $uploadedDocs[] = [
                            'path' => $storedPath,
                            'name' => $file->getClientOriginalName(),
                            'uploaded_at' => now()->toIso8601String(),
                        ];
                    }
                }
            }

            $existing = is_array($employer->documents) ? $employer->documents : ($employer->documents ? [$employer->documents] : []);
            $employer->documents = array_merge($existing, $uploadedDocs);

            $employer->update([
                'company_name'            => $validated['company_name'],
                'email'                   => $validated['email'] ?? null,
                'contact_person'          => $validated['contact_person'] ?? null,
                'phone'                   => $validated['phone'],
                'address'                 => $validated['address'],
                'documents'               => $employer->documents,
                'onboarding_completed_at' => now(),
                'approved'                => false,
                'approval_status'         => 'pending',
            ]);

            return redirect()
                ->route('onboarding.pending')
                ->with('success', 'Employer onboarding submitted successfully!');
        }

        $beneficiary = $user->beneficiary;
        if (! $beneficiary) {
            $firstName = Str::before($user->name, ' ');
            $lastName  = Str::contains($user->name, ' ')
                ? Str::after($user->name, ' ')
                : '-';
            $beneficiary = Beneficiary::create([
                'user_id'    => $user->id,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => $user->email,
                'status'     => 'draft',
            ]);
        }

        // Determine category and validate accordingly
        $category = $request->input('category') ?? $user->beneficiary_type ?? 'student';
        
        // Save beneficiary_type to User so it persists
        if ($user->beneficiary_type !== $category) {
            $user->update(['beneficiary_type' => $category]);
        }

        $rules = [
            'phone' => 'nullable|string|max:20',
        ];

        if ($category === 'student') {
            $rules['school'] = 'required|string|max:255';
        } elseif ($category === 'osy') {
            $rules['skills'] = 'required|string|max:255';
        } elseif ($category === 'dependent') {
            $rules['parentName'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        // Handle category-specific fields
        if ($category === 'student' && !empty($validated['school'])) {
            $schoolName = trim($validated['school']);
            $school = \App\Models\School::firstOrCreate(['name' => $schoolName], ['name' => $schoolName]);
            $beneficiary->school_id = $school->id;
        }

        if ($category === 'osy' && !empty($validated['skills'])) {
            $beneficiary->skills = $validated['skills'];
        }

        if ($category === 'dependent' && !empty($validated['parentName'])) {
            $beneficiary->parent_name = $validated['parentName'];
        }

        if (!empty($validated['phone'])) {
            $beneficiary->phone = $validated['phone'];
        }

        // Store any incoming files
        $uploadedDocs = [];
        if (!empty($incomingFiles)) {
            foreach ($incomingFiles as $file) {
                if (! $file) continue;
                $storedPath = $file->store('documents/users/' . Auth::id(), 'public');
                if ($storedPath) {
                    $uploadedDocs[] = [
                        'path' => $storedPath,
                        'name' => $file->getClientOriginalName(),
                        'uploaded_at' => now()->toIso8601String(),
                    ];
                }
            }
        }

        $existing = is_array($beneficiary->documents) ? $beneficiary->documents : ($beneficiary->documents ? [$beneficiary->documents] : []);
        $beneficiary->documents = array_merge($existing, $uploadedDocs);

        // Finalize beneficiary
        $beneficiary->update([
            'status'                  => 'pending',
            'onboarding_completed_at' => now(),
            'approved'                => false,
            'approval_status'         => 'pending',
            'phone'                   => $beneficiary->phone,
            'school_id'               => $beneficiary->school_id ?? null,
            'skills'                  => $beneficiary->skills ?? null,
            'parent_name'             => $beneficiary->parent_name ?? null,
            'documents'               => $beneficiary->documents,
        ]);

        return redirect()
            ->route('onboarding.pending')
            ->with('success', 'Beneficiary onboarding submitted successfully!');
    }
}
