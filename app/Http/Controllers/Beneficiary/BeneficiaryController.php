<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Models\EmployerRating;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
            'documents.*' => 'file|mimes:pdf,jpg,png'
        ]);

        $paths = [];
        foreach ($request->file('documents') as $file) {
            $paths[] = $file->store('documents');
        }

        auth()->user()->beneficiary->update([
            'documents' => $paths
        ]);

        return response()->json(['message' => 'Documents uploaded']);
    }

    // Upcoming Interviews
    public function interviews()
    {
        return Interview::whereHas('application', function ($q) {
            $q->where('beneficiary_id', auth()->id());
        })->get();
    }

    // Ratings
    public function ratings()
    {
        return EmployerRating::where('beneficiary_id', auth()->id())->get();
    }

    public function getRatings($id)
    {
        $beneficiary = auth()->user();
        $avg = $beneficiary->ratings()->avg('overall');

        return response()->json([
            'ratings' => $beneficiary->ratings,
            'average' => round($avg, 2)
        ]);
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
}
