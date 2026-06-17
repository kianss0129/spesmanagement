<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Beneficiary;
use App\Models\EmployerRating;

class BeneficiaryRatingController extends Controller
{
public function store(Request $request)
{
    $data = $request->validate([
        'beneficiary_id' => 'required|exists:beneficiaries,id',
        'application_id' => 'required|exists:applications,id',

        'punctuality' => 'required|integer|min:1|max:5',
        'work_quality' => 'required|integer|min:1|max:5',
        'work_attitude' => 'required|integer|min:1|max:5',
        'communication' => 'required|integer|min:1|max:5',
        'overall' => 'required|integer|min:1|max:5',

        'comment' => 'nullable|string',
    ]);

    $employer = auth()->user()->employer;

    if (!$employer) {
        return response()->json(['message' => 'Employer not found'], 400);
    }

    $application = Application::with('jobListing')->findOrFail($data['application_id']);

    if (
        (int) $application->beneficiary_id !== (int) $data['beneficiary_id'] ||
        (int) $application->jobListing?->employer_id !== (int) $employer->id
    ) {
        return response()->json([
            'message' => 'You can only rate beneficiaries assigned to your employer account.',
        ], 403);
    }

    EmployerRating::create([
        'employer_id' => $employer->id,
        'beneficiary_id' => $data['beneficiary_id'],
        'application_id' => $data['application_id'],

        'punctuality' => $data['punctuality'] ?? 5,
        'work_quality' => $data['work_quality'] ?? 5,

        // 🔥 FIX HERE
        'attitude' => $data['work_attitude'] ?? 5,

        'communication' => $data['communication'] ?? 5,
        'overall' => $data['overall'] ?? 5,

       'comment' => $data['comment'] ?? null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Rating submitted successfully'
    ]);
}

    public function markCompleted($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);
        $employer = auth()->user()->employer;

        if (! $employer) {
            return response()->json(['message' => 'Employer not found.'], 403);
        }

        $application = Application::with('jobListing')
            ->where('beneficiary_id', $beneficiary->id)
            ->whereHas('jobListing', fn ($query) => $query->where('employer_id', $employer->id))
            ->whereIn('status', ['contract_signed', 'deployed', 'ongoing'])
            ->latest()
            ->first();

        if (! $application) {
            return response()->json([
                'message' => 'No eligible application found for completion review.',
            ], 422);
        }

        $beneficiary->employment_status = 'employed';
        $beneficiary->save();

        $application->update(['status' => 'completion_review']);

        return response()->json([
            'success' => true,
            'message' => 'Beneficiary submitted for CPESO completion review'
        ]);
    }

    public function index()
    {
        $employer = auth()->user()->employer;

        if (!$employer) {
            return response()->json([]);
        }

        $beneficiaries = Beneficiary::where('approval_status', 'approved')
            ->where('employer_id', $employer->id)
            ->with('user')
            ->get();

        return response()->json($beneficiaries);
    }
}
