<?php

namespace App\Http\Controllers\PESO;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\Employer;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function dashboard()
    {
        $applicantsBySchool = Beneficiary::join('schools', 'beneficiaries.school_id', '=', 'schools.id')
            ->select('schools.name as school_name', DB::raw('count(beneficiaries.id) as total'))
            ->groupBy('schools.id', 'schools.name')
            ->orderByDesc('total')
            ->get();

        $topEmployers = Employer::join('job_listings', 'job_listings.employer_id', '=', 'employers.id')
            ->join('applications', 'applications.job_listing_id', '=', 'job_listings.id')
            ->where('applications.status', 'hired')
            ->selectRaw('employers.id, employers.name as employer_name, count(applications.id) as total')
            ->groupBy('employers.id', 'employers.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return response()->json([
            'applicantsBySchool' => $applicantsBySchool,
            'topEmployers' => $topEmployers,
            // Include applicant trends if needed
            'applicantTrends' => ['labels'=>[], 'data'=>[]]
        ]);
    }
}
