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

    /**
     * STEP 1: Basic info
     */
    public function step1(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        // Update user
        $user->update($validated);

        // Split name safely
        $firstName = Str::before($validated['name'], ' ');
        $lastName  = Str::contains($validated['name'], ' ')
            ? Str::after($validated['name'], ' ')
            : '-';

        // Create or update beneficiary
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

    /**
     * STEP 2: Category-specific info
     */
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

        // If student provided a school name, find or create School and save school_id
        if ($category === 'student' && isset($validated['school'])) {
            $schoolName = trim($validated['school']);
            $school = \App\Models\School::firstOrCreate([
                'name' => $schoolName,
            ], [
                'name' => $schoolName,
            ]);

            $beneficiary->update([
                'school_id' => $school->id,
            ]);
        } else {
            // other categories store raw fields on beneficiary
            $beneficiary->update($validated);
        }

        return response()->json(['message' => 'Step 2 saved']);
    }

    /**
     * Upload documents
     */
    public function upload(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $files = [];

        if ($request->hasFile('documents')) {
            // Determine upload folder based on user type
            $folder = $user->hasRole('Employer') ? 'documents/employers' : 'documents/users';
            $uploadDir = $folder . '/' . Auth::id();

            foreach ($request->file('documents') as $file) {
                // Store file to public disk with relative path
                $storedPath = $file->store($uploadDir, 'public');
                if ($storedPath) {
                    $files[] = $storedPath;
                }
            }
        }

        // Handle both employer and beneficiary uploads
        if ($user->hasRole('Employer') || $request->filled('employer_upload')) {
            // Save to employer
            $employer = $user->employer ?? $user->employer()->create([]);

            $employer->documents = array_merge(
                $employer->documents ?? [],
                $files
            );

            $employer->save();
        } else {
            // Save to beneficiary (default)
            $beneficiary = $user->beneficiary;

            abort_if(! $beneficiary, 404);

            $beneficiary->documents = array_merge(
                $beneficiary->documents ?? [],
                $files
            );

            $beneficiary->save();
        }

        return response()->json(['message' => 'Documents uploaded']);
    }

    /**
     * FINAL SUBMIT
     */
    public function submit(Request $request)
    {
        $user = Auth::user();

        /**
         * EMPLOYER ONBOARDING
         */
        if ($request->filled('company_name')) {

            $validated = $request->validate([
                'company_name'  => 'required|string|max:255',
                'email'         => 'nullable|email|max:255',
                'contact_person' => 'nullable|string|max:255',
                'phone'         => 'required|string|max:20',
                'address'       => 'nullable|string|max:255',
            ]);

            if (! $user->hasRole('Employer')) {
                $user->assignRole('Employer');
            }

            $user->update([
                'name' => $validated['company_name'],
            ]);

            $employer = $user->employer ?? $user->employer()->create([]);

            // Get uploaded documents from user's temporary documents field if it exists
            $documents = [];
            if ($request->filled('documents')) {
                $documents = is_array($request->input('documents')) ? $request->input('documents') : [];
            }

            $employer->update([
                'company_name'            => $validated['company_name'],
                'email'                   => $validated['email'] ?? null,
                'contact_person'          => $validated['contact_person'] ?? null,
                'phone'                   => $validated['phone'],
                'address'                 => $validated['address'],
                'documents'               => $documents,
                'onboarding_completed_at' => now(),
                'approved'                => false,
            ]);

            return redirect()
                ->route('onboarding.pending')
                ->with('success', 'Employer onboarding submitted successfully!');
        }

        /**
         * BENEFICIARY ONBOARDING
         */
        $beneficiary = $user->beneficiary;

        // Ensure beneficiary exists (safety net)
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

        // Final validation: accept optional phone and school fields and handle documents
        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'school' => 'nullable|string|max:255',
            'documents' => 'nullable', // expecting array of stored paths if present
        ]);

        // If school provided on final submit, try to find/create and save school_id
        if (!empty($validated['school'])) {
            $schoolName = trim($validated['school']);
            $school = \App\Models\School::firstOrCreate(['name' => $schoolName], ['name' => $schoolName]);
            $beneficiary->school_id = $school->id;
        }

        // Update phone if provided
        if (!empty($validated['phone'])) {
            $beneficiary->phone = $validated['phone'];
        }

        // Merge documents if passed as input (don't overwrite existing stored documents)
        if ($request->filled('documents')) {
            $incoming = is_array($request->input('documents')) ? $request->input('documents') : [];
            $beneficiary->documents = array_values(array_unique(array_merge($beneficiary->documents ?? [], $incoming)));
        }

        $beneficiary->status = 'pending';
        $beneficiary->onboarding_completed_at = now();
        $beneficiary->approved = false;
        $beneficiary->save();

        return redirect()
            ->route('onboarding.pending')
            ->with('success', 'Beneficiary onboarding submitted successfully!');
    }
}
