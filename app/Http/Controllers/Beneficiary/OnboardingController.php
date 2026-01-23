<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $beneficiary->update($validated);

        return response()->json(['message' => 'Step 2 saved']);
    }

    /**
     * Upload documents
     */
    public function upload(Request $request)
    {
        $beneficiary = Auth::user()->beneficiary;

        abort_if(! $beneficiary, 404);

        $request->validate([
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $files = [];

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $files[] = $file->store(
                    'documents/beneficiaries/' . Auth::id(),
                    'public'
                );
            }
        }

        $beneficiary->documents = array_merge(
            $beneficiary->documents ?? [],
            $files
        );

        $beneficiary->save();

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
                'company_name' => 'required|string|max:255',
                'phone'        => 'required|string|max:20',
                'address'      => 'nullable|string|max:255',
            ]);

            if (! $user->hasRole('Employer')) {
                $user->assignRole('Employer');
            }

            $user->update([
                'name' => $validated['company_name'],
            ]);

            $employer = $user->employer ?? $user->employer()->create([]);

            $employer->update([
                'company_name'            => $validated['company_name'],
                'phone'                   => $validated['phone'],
                'address'                 => $validated['address'],
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

        // Final validation if needed
        $validated = $request->validate([
            // add required fields before submission if needed
        ]);

        $beneficiary->update($validated + [
            'status'                     => 'pending',
            'onboarding_completed_at'    => now(),
            'approved'                   => false,
        ]);

        return redirect()
            ->route('onboarding.pending')
            ->with('success', 'Beneficiary onboarding submitted successfully!');
    }
}
