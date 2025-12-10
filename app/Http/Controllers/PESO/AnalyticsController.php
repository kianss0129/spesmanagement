<?php

namespace App\Http\Controllers\PESO;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\Employer;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function applicantsBySchool()
    {
        $data = Beneficiary::selectRaw('school_id, count(*) as total')
            ->groupBy('school_id')
            ->orderByDesc('total')
            ->get();

        return response()->json($data);
    }

    public function topHiringEmployers()
    {
        $data = Employer::join('job_listings', 'job_listings.employer_id', '=', 'employers.id')
            ->join('applications', 'applications.job_listing_id', '=', 'job_listings.id')
            ->where('applications.status', 'hired')
            ->selectRaw('employers.id, employers.name, count(applications.id) as hires')
            ->groupBy('employers.id', 'employers.name')
            ->orderByDesc('hires')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    public function performanceTrends()
    {
        $data = DB::table('employer_ratings')
            ->join('applications', 'applications.id', '=', 'employer_ratings.application_id')
            ->join('job_listings', 'job_listings.id', '=', 'applications.job_listing_id')
            ->join('employers', 'employers.id', '=', 'job_listings.employer_id')
            ->selectRaw('employers.id, employers.name, avg(employer_ratings.overall) as avg_rating')
            ->groupBy('employers.id', 'employers.name')
            ->get();

        return response()->json($data);
    }
}
