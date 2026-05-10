<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Show the profile edit page.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        $beneficiary = null;
        $ratings = [];
        $average = 0;

        if ($user->hasRole('Beneficiary') && $user->beneficiary) {

            // Load beneficiary with relations
            $beneficiaryModel = $user->beneficiary()
                ->with([
                    'school',
                    'pesoOffice',
                    'workHistory.employer'
                ])
                ->first();

            if ($beneficiaryModel) {
                // Convert to array to include all fields
                $beneficiary = $beneficiaryModel->toArray();

                // Flatten school fields for easier frontend display
                $beneficiary['school'] = [
                    'name' => $beneficiary['school']['name'] ?? 'N/A',
                    'program' => $beneficiary['program'] ?? 'N/A',
                    'year_level' => $beneficiary['year_level'] ?? 'N/A',
                    'student_id' => $beneficiary['student_id'] ?? 'N/A',
                ];
            }

            // Load ratings and calculate average
            $ratings = $beneficiaryModel->ratings()
                ->with(['employer', 'application.jobListing'])
                ->get();

            $average = $ratings->avg('overall') ?? 0;
        }

        return Inertia::render('Profile/Show', [
            'confirmsTwoFactorAuthentication' => config('fortify.features.two_factor_authentication'),
            'sessions' => $request->session()->all(),
            'beneficiary' => $beneficiary,
            'ratings' => $ratings,
            'average' => round($average, 1),
        ]);
    }

    /**
     * Update beneficiary profile.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('Beneficiary') && $user->beneficiary) {
            $beneficiary = $user->beneficiary;

            // Validate personal and school info
            $data = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'birthdate' => 'nullable|date',
                'gender' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'program' => 'nullable|string|max:255',
                'year_level' => 'nullable|string|max:50',
                'student_id' => 'nullable|string|max:50',
            ]);

            $beneficiary->update($data);

            return redirect()->back()->with('success', 'Profile updated successfully!');
        }

        return redirect()->back()->with('error', 'Unauthorized');
    }

    /**
     * Delete user account.
     */
    public function destroy()
    {
        Auth::user()->delete();
        return redirect('/')->with('success', 'Account deleted successfully!');
    }
}