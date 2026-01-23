<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Beneficiary;

class OnboardingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        return inertia('Beneficiary/Onboarding', [
            'user' => $user,
            'category' => $request->query(
                'category',
                $user->beneficiary_type ?? 'student'
            ),
        ]);
    }

    public function step1(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only('name', 'email'));

        $beneficiary = $user->beneficiary;
        $beneficiary->update([
            'first_name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json(['message' => 'Step 1 saved']);
    }

    public function step2(Request $request)
    {
        $beneficiary = Auth::user()->beneficiary;
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

    public function upload(Request $request)
    {
        $beneficiary = Auth::user()->beneficiary;

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

    public function submit()
{
    $beneficiary = Auth::user()->beneficiary;

    $beneficiary->update([
        'onboarding_completed_at' => now(),
        'approved' => false, // pending approval
    ]);

    return redirect()->route('onboarding.pending');
}

}
