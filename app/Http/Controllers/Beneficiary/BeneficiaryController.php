<?php

namespace App\Http\Controllers\Beneficiary;
use App\Models\Beneficiary;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Models\EmployerRating;
use Illuminate\Http\Request;

class BeneficiaryController extends Controller
{
    // ✅ Apply to Job
    public function apply(Request $request)
    {
        $data = $request->validate([
            'job_listing_id' => 'required'
        ]);

        $data['beneficiary_id'] = auth()->user()->id;

        Application::create($data);

        return response()->json(['message' => 'Application submitted']);
    }

    // ✅ Upload Documents
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

    // ✅ View Interview
    public function interviews()
    {
        return Interview::whereHas('application', function ($q) {
            $q->where('beneficiary_id', auth()->user()->id);
        })->get();
    }

    // ✅ View Ratings
    public function ratings()
    {
        return EmployerRating::where('beneficiary_id', auth()->user()->id)->get();
    }

 public function getRatings($id)
    {
        $beneficiary = Beneficiary::with(['ratings.employer','ratings.application'])->findOrFail($id);

        $avg = $beneficiary->ratings()->avg('overall');

        return response()->json([
            'ratings' => $beneficiary->ratings,
            'average' => round($avg,2)
        ]);
    }

}
