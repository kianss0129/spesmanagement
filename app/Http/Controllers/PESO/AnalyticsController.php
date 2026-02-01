<?php

namespace App\Http\Controllers\PESO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Beneficiary;
use App\Models\Employer;
use App\Models\Application;
use App\Models\EmployerRating;
use App\Models\Batch;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    // Dashboard JSON response
    public function dashboard(Request $request)
    {
        return response()->json([
            'applicantsBySchool' => $this->applicantsBySchool($request),
            'topEmployers' => $this->topHiringEmployers(),
            'performanceTrends' => $this->performanceTrends($request),
            'completionRate' => $this->completionRatePerBatch(),
            'attendanceCompliance' => $this->attendanceCompliance(),
        ]);
    }

    // 1️⃣ Applicants by School
    public function applicantsBySchool(Request $request)
    {
        $period = $request->query('period', 'year');
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        $query = Application::join('beneficiaries','applications.beneficiary_id','=','beneficiaries.id')
                            ->join('schools','beneficiaries.school_id','=','schools.id');

        if ($start && $end) {
            $query->whereBetween('applications.created_at', [Carbon::parse($start)->startOfDay(), Carbon::parse($end)->endOfDay()]);
        } else {
            if ($period === 'month') {
                $query->whereMonth('applications.created_at', now()->month)->whereYear('applications.created_at', now()->year);
            } elseif ($period === 'semester') {
                $month = now()->month;
                $year = now()->year;
                if ($month <= 6) {
                    $query->whereBetween('applications.created_at', ["{$year}-01-01","{$year}-06-30"]);
                } else {
                    $query->whereBetween('applications.created_at', ["{$year}-07-01","{$year}-12-31"]);
                }
            } else {
                $query->whereYear('applications.created_at', now()->year);
            }
        }

        $data = $query->select('schools.name as school', DB::raw('COUNT(*) as total'))
                      ->groupBy('schools.name')
                      ->orderByDesc('total')
                      ->get();

        return [
            'labels' => $data->pluck('school')->toArray(),
            'data' => $data->pluck('total')->toArray()
        ];
    }

    // 2️⃣ Top Hiring Employers
    public function topHiringEmployers()
    {
        $data = Employer::select('employers.id','employers.company_name as name', DB::raw('COUNT(applications.id) as hires'))
            ->join('job_listings','job_listings.employer_id','=','employers.id')
            ->join('applications','applications.job_listing_id','=','job_listings.id')
            ->groupBy('employers.id','employers.company_name')
            ->orderByDesc('hires')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('name'),
            'data' => $data->pluck('hires')
        ];
    }

    // 3️⃣ Performance Rating Trends
    public function performanceTrends(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $period = $request->query('period', 'year');

        if ($start && $end) {
            $startDate = Carbon::parse($start)->startOfMonth();
            $endDate = Carbon::parse($end)->endOfMonth();
        } else {
            $now = Carbon::now();
            if ($period === 'month') {
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
            } elseif ($period === 'semester') {
                $month = $now->month;
                $year = $now->year;
                if ($month <= 6) {
                    $startDate = Carbon::parse("{$year}-01-01");
                    $endDate = Carbon::parse("{$year}-06-30");
                } else {
                    $startDate = Carbon::parse("{$year}-07-01");
                    $endDate = Carbon::parse("{$year}-12-31");
                }
            } else {
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
            }
        }

        // build months between
        $periodMonths = [];
        $cursor = $startDate->copy()->startOfMonth();
        while ($cursor->lte($endDate)) {
            $periodMonths[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        $labels = array_map(function($ym){ return Carbon::parse($ym.'-01')->format('M Y'); }, $periodMonths);

        // query averages grouped by employer + ym
        $ratings = EmployerRating::select('employer_id', DB::raw('AVG(overall) as avg_rating'), DB::raw('DATE_FORMAT(created_at, "%Y-%m") as ym'))
            ->whereBetween('created_at', [$startDate->toDateString().' 00:00:00', $endDate->toDateString().' 23:59:59'])
            ->groupBy('employer_id', 'ym')
            ->get();

        $employers = Employer::pluck('name','id')->toArray();

        $grouped = [];
        foreach ($ratings as $r) {
            $grouped[$r->employer_id][$r->ym] = round((float)$r->avg_rating, 2);
        }

        $series = [];
        foreach ($employers as $id => $name) {
            $data = [];
            foreach ($periodMonths as $ym) {
                $data[] = $grouped[$id][$ym] ?? 0;
            }
            $series[] = ['name' => $name, 'data' => $data];
        }

        return ['labels' => $labels, 'series' => $series];
    }

    // 4️⃣ Completion Rate per Batch
    public function completionRatePerBatch()
    {
        $batches = Batch::withCount(['applications as completed_count' => function($q){
            $q->where('status','completed');
        }])->withCount('applications')->get();

        $labels = $batches->pluck('name')->toArray();
        $data = $batches->map(function($batch){
            return $batch->applications_count ? round($batch->completed_count / $batch->applications_count * 100, 2) : 0;
        })->toArray();

        return ['labels' => $labels, 'data' => $data];
    }

    // 5️⃣ Attendance Compliance
    public function attendanceCompliance(Request $request)
    {
        $batchId = $request->query('batch_id');
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $requiredDays = (int) $request->query('required_days', 20);

        $beneficiaryIds = [];
        if ($batchId) {
            $beneficiaryIds = Application::where('batch_id', $batchId)->pluck('beneficiary_id')->unique()->toArray();
        }

        $query = Beneficiary::query();
        if (!empty($beneficiaryIds)) $query->whereIn('id', $beneficiaryIds);

        $beneficiaries = $query->get();

        $labels = [];
        $data = [];

        foreach ($beneficiaries as $b) {
            $attQuery = $b->attendances();
            if ($start && $end) {
                $attQuery->whereBetween('date', [Carbon::parse($start)->toDateString(), Carbon::parse($end)->toDateString()]);
            }
            $count = $attQuery->count();
            $percent = $requiredDays ? round($count / $requiredDays * 100, 2) : 0;

            $labels[] = $b->name;
            $data[] = $percent;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    // 6️⃣ High-Rated Beneficiaries
    public function highRatedBeneficiaries(Request $request)
    {
        $top = EmployerRating::select('beneficiary_id', DB::raw('AVG(overall) as avg_rating'))
            ->groupBy('beneficiary_id')
            ->orderByDesc('avg_rating')
            ->limit(10)
            ->get();

        return $top->map(function($b){
            $beneficiary = Beneficiary::find($b->beneficiary_id);
            $feedback = EmployerRating::where('beneficiary_id',$b->beneficiary_id)
                        ->get(['punctuality','attitude','output','communication','overall']);
            return [
                'beneficiary_name' => $beneficiary->name,
                'average_rating' => round($b->avg_rating,2),
                'feedback' => $feedback
            ];
        });
    }

    // 4️⃣ Completion Rate (Overall)
    public function completionRate(Request $request)
   {
       try {
           $batchId = $request->query('batch_id');
           $period = $request->query('period', 'month');
           $start = $request->query('start_date');
           $end = $request->query('end_date');

           // Build base query for applications
           $query = Application::query();

           // Filter by batch if provided
           if ($batchId) {
               $query->where('batch_id', $batchId);
           }

           // Apply date filters
           if ($start && $end) {
               $query->whereBetween('created_at', [
                   Carbon::parse($start)->startOfDay(),
                   Carbon::parse($end)->endOfDay()
               ]);
           } else {
               // Default period filters
               if ($period === 'month') {
                   $query->whereMonth('created_at', now()->month)
                         ->whereYear('created_at', now()->year);
               } elseif ($period === 'week') {
                   $query->whereBetween('created_at', [
                       now()->startOfWeek(),
                       now()->endOfWeek()
                   ]);
               }
           }

           // Verify that required columns exist before using them
           $applications = $query->select('id', 'status', 'created_at', 'approved_at')
                                   ->get();

           // Handle empty dataset
           if ($applications->isEmpty()) {
               return response()->json([
                   'labels' => [],
                   'data' => [],
                   'message' => 'No applications found for the given criteria'
               ], 200);
           }

           // Group by period
           $grouped = [];
           $labels = [];

           foreach ($applications as $app) {
               $dateKey = $period === 'month'
                   ? $app->created_at->format('Y-m')
                   : $app->created_at->format('Y-m-d');

               if (!isset($grouped[$dateKey])) {
                   $grouped[$dateKey] = ['total' => 0, 'completed' => 0];
               }

               $grouped[$dateKey]['total']++;
               if ($app->status === 'completed' || $app->status === 'approved') {
                   $grouped[$dateKey]['completed']++;
               }
           }

           // Generate labels and data
           foreach ($grouped as $dateKey => $counts) {
               $labels[] = $dateKey;
               // Prevent division by zero
               $rate = $counts['total'] > 0 
                   ? round(($counts['completed'] / $counts['total']) * 100, 2) 
                   : 0;
               $data[] = $rate;
           }

           return response()->json([
               'labels' => $labels,
               'data' => $data
           ], 200);

       } catch (\Exception $e) {
           \Log::error('Error in completionRate method: ' . $e->getMessage(), [
               'exception' => $e,
               'request' => $request->all()
           ]);

           return response()->json([
               'labels' => [],
               'data' => [],
               'error' => 'An error occurred while fetching completion rate data'
           ], 200);
       }
   }
}
