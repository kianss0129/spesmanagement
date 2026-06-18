<?php

namespace App\Http\Controllers\PESO;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Attendance;
use App\Models\Batch;
use App\Models\Beneficiary;
use App\Models\Employer;
use App\Models\EmployerRating;
use App\Models\School;
use App\Models\User;
use App\Models\WorkOutput;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function dashboard(Request $request)
    {
        try {
            $filters = $this->filters($request);

            return response()->json([
                'applicantsBySchool' => $this->applicantsBySchool($request),
                'topEmployers' => $this->topHiringEmployers(),
                'performanceTrends' => $this->performanceTrends($request),
                'completionRate' => $this->completionRatePerBatch(),
                'attendanceCompliance' => $this->attendanceCompliance($request),
                'stats' => $this->dashboardStats($request),
                'reporting' => [
                    'summary' => $this->summaryCards($filters),
                    'charts' => $this->charts($filters),
                    'reports' => $this->reports($filters),
                    'insights' => $this->insights($filters),
                    'filters' => $this->filterOptions(),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'applicantsBySchool' => ['labels' => [], 'data' => []],
                'topEmployers' => ['labels' => [], 'data' => []],
                'performanceTrends' => ['labels' => [], 'series' => []],
                'completionRate' => ['labels' => [], 'data' => []],
                'attendanceCompliance' => ['labels' => [], 'data' => []],
                'stats' => ['chart_dates' => [], 'users_growth' => [], 'applications_by_peso' => []],
                'reporting' => $this->emptyReporting(),
            ], 200);
        }
    }

    public function dashboardStats(Request $request): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $dates = [];
        $cursor = $startDate->copy();

        while ($cursor->lte($endDate)) {
            $dates[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        $users = User::whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        return [
            'chart_dates' => $dates,
            'users_growth' => array_map(fn ($date) => $users[$date] ?? 0, $dates),
            'applications_by_peso' => [],
        ];
    }

    public function resolveDateRange(Request $request): array
    {
        $filter = $request->query('date_filter', 'last_7_days');
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        if ($filter === 'custom' && $start && $end) {
            return [Carbon::parse($start)->startOfDay(), Carbon::parse($end)->endOfDay()];
        }

        $now = Carbon::now();

        return match ($filter) {
            'today' => [Carbon::today(), Carbon::today()],
            'yesterday' => [Carbon::yesterday(), Carbon::yesterday()],
            'last_3_days' => [Carbon::today()->subDays(2), Carbon::today()],
            'this_week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'last_week' => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'ytd' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [Carbon::today()->subDays(6), Carbon::today()],
        };
    }

    public function applicantsBySchool(Request $request): array
    {
        $query = Application::query()
            ->leftJoin('beneficiaries', 'applications.beneficiary_id', '=', 'beneficiaries.id')
            ->leftJoin('schools', 'beneficiaries.school_id', '=', 'schools.id');

        $this->applyDateRange($query, 'applications.created_at', $this->filters($request));

        $data = $query
            ->selectRaw('COALESCE(schools.name, beneficiaries.school_name, "Unspecified") as label, COUNT(*) as total')
            ->groupBy('schools.name', 'beneficiaries.school_name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return ['labels' => $data->pluck('label')->all(), 'data' => $data->pluck('total')->map(fn ($v) => (int) $v)->all()];
    }

    public function topEmployers(): array
    {
        return $this->topHiringEmployers();
    }

    public function topHiringEmployers(): array
    {
        $data = Application::query()
            ->leftJoin('job_listings', 'applications.job_listing_id', '=', 'job_listings.id')
            ->leftJoin('employers', 'job_listings.employer_id', '=', 'employers.id')
            ->selectRaw('COALESCE(employers.company_name, "Unassigned") as label, COUNT(*) as total')
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return ['labels' => $data->pluck('label')->all(), 'data' => $data->pluck('total')->map(fn ($v) => (int) $v)->all()];
    }

    public function performanceTrends(Request $request): array
    {
        $filters = $this->filters($request);
        $months = $this->months($filters);
        $ratings = EmployerRating::query()
            ->selectRaw('employer_id, AVG(overall) as avg_rating, DATE_FORMAT(created_at, "%Y-%m") as ym')
            ->when($filters['start_date'] && $filters['end_date'], fn ($q) => $q->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]))
            ->groupBy('employer_id', 'ym')
            ->get()
            ->groupBy('employer_id');

        $employers = Employer::orderBy('company_name')->limit(6)->pluck('company_name', 'id');

        return [
            'labels' => array_map(fn ($ym) => Carbon::parse($ym . '-01')->format('M Y'), $months),
            'series' => $employers->map(function ($name, $id) use ($ratings, $months) {
                $byMonth = ($ratings[$id] ?? collect())->pluck('avg_rating', 'ym');
                return [
                    'name' => $name,
                    'data' => array_map(fn ($ym) => round((float) ($byMonth[$ym] ?? 0), 2), $months),
                ];
            })->values()->all(),
        ];
    }

    public function completionRatePerBatch(): array
    {
        $batches = Batch::query()
            ->withCount(['applications', 'applications as completed_count' => fn ($q) => $q->where('status', 'completed')])
            ->get();

        return [
            'labels' => $batches->pluck('name')->all(),
            'data' => $batches->map(fn ($batch) => $batch->applications_count ? round($batch->completed_count / $batch->applications_count * 100, 2) : 0)->all(),
        ];
    }

    public function attendanceCompliance(Request $request): array
    {
        $filters = $this->filters($request);
        $requiredDays = max((int) $request->query('required_days', 20), 1);

        $data = Beneficiary::query()
            ->select('beneficiaries.id', DB::raw('CONCAT_WS(" ", beneficiaries.first_name, beneficiaries.last_name) as name'))
            ->withCount(['attendances as attendance_count' => function ($query) use ($filters) {
                $this->applyDateRange($query, 'date', $filters, false);
            }])
            ->orderByDesc('attendance_count')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('name')->map(fn ($name) => $name ?: 'Unnamed')->all(),
            'data' => $data->map(fn ($row) => round(($row->attendance_count / $requiredDays) * 100, 2))->all(),
        ];
    }

    public function averageBeneficiaryRatings(Request $request): array
    {
        $averages = EmployerRating::selectRaw(
            'AVG(punctuality) as punctuality, AVG(output_quality) as work_quality, AVG(work_attitude) as attitude, AVG(communication) as communication, AVG(overall) as overall'
        )->first();

        return [
            'punctuality' => round($averages->punctuality ?? 0, 2),
            'work_quality' => round($averages->work_quality ?? 0, 2),
            'attitude' => round($averages->attitude ?? 0, 2),
            'communication' => round($averages->communication ?? 0, 2),
            'overall' => round($averages->overall ?? 0, 2),
            'submitted_count' => EmployerRating::count(),
        ];
    }

    public function completionRate(Request $request)
    {
        $filters = $this->filters($request);
        $total = $this->filteredApplications($filters)->count();
        $completed = $this->filteredApplications($filters)->where('applications.status', 'completed')->count();

        return response()->json([
            'labels' => ['Completion Rate'],
            'data' => [$total ? round(($completed / $total) * 100, 2) : 0],
        ]);
    }

    private function summaryCards(array $filters): array
    {
        $applicants = $this->filteredBeneficiaries($filters)
            ->whereNotNull('beneficiaries.submitted_at');
        $beneficiaries = $this->filteredBeneficiaries($filters);
        $attendances = $this->filteredAttendances($filters);
        $workOutputs = $this->filteredWorkOutputs($filters);

        return [
            'total_applicants' => $applicants->distinct('beneficiaries.id')->count('beneficiaries.id'),
            'approved_beneficiaries' => $this->filteredBeneficiaries($filters)->where(function ($q) {
                $q->where('beneficiaries.approved', true)->orWhere('beneficiaries.approval_status', 'approved')->orWhere('beneficiaries.status', 'approved');
            })->distinct('beneficiaries.id')->count('beneficiaries.id'),
            'students' => $this->categoryCount($filters, ['student', 'students']),
            'osy' => $this->categoryCount($filters, ['osy', 'out_of_school_youth', 'out of school youth']),
            'ddw' => $this->categoryCount($filters, $this->ddwCategories()),
            'participating_employers' => $this->filteredEmployers($filters)->count(),
            'schools_represented' => $beneficiaries->where(function ($q) {
                $q->whereNotNull('beneficiaries.school_id')->orWhereNotNull('beneficiaries.school_name');
            })->distinct('beneficiaries.school_id')->count('beneficiaries.school_id') ?: $beneficiaries->distinct('beneficiaries.school_name')->count('beneficiaries.school_name'),
            'ongoing_beneficiaries' => $this->ongoingBeneficiaries($filters)->distinct('beneficiaries.id')->count('beneficiaries.id'),
            'completed_beneficiaries' => $this->completedBeneficiaries($filters)->distinct('beneficiaries.id')->count('beneficiaries.id'),
            'rejected_applicants' => $this->filteredApplications($filters)->where('applications.status', 'rejected')->count(),
            'pending_applications' => $this->filteredApplications($filters)->whereIn('applications.status', ['pending', 'applied', 'submitted', 'screening'])->count(),
            'dtr_submitted' => $attendances->count(),
            'daily_reports_submitted' => $workOutputs->count(),
        ];
    }

    private function charts(array $filters): array
    {
        return [
            'beneficiaries_per_company' => $this->beneficiariesPerCompany($filters),
            'beneficiaries_per_school' => $this->beneficiariesPerSchool($filters),
            'beneficiary_categories' => $this->beneficiaryCategories($filters),
            'application_status_distribution' => $this->applicationStatusDistribution($filters),
            'applications_per_month' => $this->applicationsPerMonth($filters),
            'completed_beneficiaries_per_month' => $this->completedPerMonth($filters),
            'dtr_status_summary' => $this->dtrStatusSummary($filters),
            'daily_report_status_summary' => $this->dailyReportStatusSummary($filters),
            'top_participating_employers' => $this->beneficiariesPerCompany($filters, 10),
            'top_schools_with_most_beneficiaries' => $this->beneficiariesPerSchool($filters, 10),
        ];
    }

    private function reports(array $filters): array
    {
        return [
            'company' => $this->companyReport($filters),
            'school' => $this->schoolReport($filters),
            'category' => $this->categoryReport($filters),
            'application' => $this->applicationReport($filters),
            'attendance' => $this->attendanceReport($filters),
            'daily_report' => $this->dailyReport($filters),
            'employer_participation' => $this->employerParticipationReport($filters),
        ];
    }

    private function insights(array $filters): array
    {
        $topCompany = $this->beneficiariesPerCompany($filters, 1);
        $topSchoolApplicants = $this->applicantsBySchoolReport($filters, 1)->first();
        $summary = $this->summaryCards($filters);
        $submittedDtr = $summary['dtr_submitted'];
        $approvedDtr = $this->filteredAttendances($filters)->where('attendances.status', 'approved')->count();
        $mostActiveEmployer = $this->companyReport($filters)->sortByDesc('evaluation_count')->first();
        $representedSchool = $this->beneficiariesPerSchool($filters, 1);

        return [
            ['label' => 'Company with highest beneficiaries', 'value' => $topCompany['labels'][0] ?? 'No data', 'meta' => ($topCompany['data'][0] ?? 0) . ' beneficiaries'],
            ['label' => 'School with highest applicants', 'value' => $topSchoolApplicants->school_name ?? 'No data', 'meta' => (int) ($topSchoolApplicants->total_applicants ?? 0) . ' applicants'],
            ['label' => 'Completion rate', 'value' => $summary['approved_beneficiaries'] ? round(($summary['completed_beneficiaries'] / $summary['approved_beneficiaries']) * 100, 1) . '%' : '0%', 'meta' => $summary['completed_beneficiaries'] . ' completed'],
            ['label' => 'DTR compliance', 'value' => $submittedDtr ? round(($approvedDtr / $submittedDtr) * 100, 1) . '%' : '0%', 'meta' => $approvedDtr . ' approved DTR'],
            ['label' => 'Most active employer', 'value' => $mostActiveEmployer['company_name'] ?? 'No data', 'meta' => (int) ($mostActiveEmployer['evaluation_count'] ?? 0) . ' evaluations'],
            ['label' => 'Most represented school', 'value' => $representedSchool['labels'][0] ?? 'No data', 'meta' => ($representedSchool['data'][0] ?? 0) . ' beneficiaries'],
        ];
    }

    private function filterOptions(): array
    {
        return [
            'employers' => Employer::orderBy('company_name')->get(['id', 'company_name as name']),
            'schools' => School::orderBy('name')->get(['id', 'name']),
            'categories' => Beneficiary::whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->values(),
            'statuses' => Application::whereNotNull('status')->distinct()->orderBy('status')->pluck('status')->values(),
            'batches' => Batch::orderByDesc('start_date')->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function companyReport(array $filters)
    {
        return Employer::query()
            ->leftJoin('job_listings', 'job_listings.employer_id', '=', 'employers.id')
            ->leftJoin('applications', 'applications.job_listing_id', '=', 'job_listings.id')
            ->leftJoin('beneficiaries', 'applications.beneficiary_id', '=', 'beneficiaries.id')
            ->leftJoin('attendances', 'attendances.application_id', '=', 'applications.id')
            ->leftJoin('employer_ratings', 'employer_ratings.application_id', '=', 'applications.id')
            ->when($filters['employer_id'], fn ($q) => $q->where('employers.id', $filters['employer_id']))
            ->when($filters['batch_id'], fn ($q) => $q->where('applications.batch_id', $filters['batch_id']))
            ->when($filters['status'], fn ($q) => $q->where('applications.status', $filters['status']))
            ->when($filters['category'], fn ($q) => $q->whereRaw('LOWER(beneficiaries.category) = ?', [strtolower($filters['category'])]))
            ->when($filters['start_date'] && $filters['end_date'], fn ($q) => $q->whereBetween('applications.created_at', [$filters['start_date'], $filters['end_date']]))
            ->selectRaw('
                employers.id,
                employers.company_name,
                COUNT(DISTINCT applications.beneficiary_id) as total_beneficiaries,
                COUNT(DISTINCT CASE WHEN applications.status != "completed" AND beneficiaries.completed_at IS NULL THEN applications.beneficiary_id END) as ongoing_beneficiaries,
                COUNT(DISTINCT CASE WHEN applications.status = "completed" OR beneficiaries.completed_at IS NOT NULL THEN applications.beneficiary_id END) as completed_beneficiaries,
                COALESCE(SUM(TIMESTAMPDIFF(MINUTE, attendances.time_in, attendances.time_out)) / 60, 0) as average_rendered_hours,
                ROUND(AVG(employer_ratings.overall), 2) as average_rating,
                COUNT(DISTINCT employer_ratings.id) as evaluation_count
            ')
            ->groupBy('employers.id', 'employers.company_name')
            ->orderByDesc('total_beneficiaries')
            ->get();
    }

    private function schoolReport(array $filters)
    {
        return $this->applicantsBySchoolReport($filters)->map(fn ($row) => [
            'school_name' => $row->school_name,
            'total_applicants' => (int) $row->total_applicants,
            'total_approved' => (int) $row->total_approved,
            'total_ongoing' => (int) $row->total_ongoing,
            'total_completed' => (int) $row->total_completed,
        ])->values();
    }

    private function categoryReport(array $filters): array
    {
        return [
            'student_count' => $this->categoryCount($filters, ['student', 'students']),
            'osy_count' => $this->categoryCount($filters, ['osy', 'out_of_school_youth', 'out of school youth']),
            'ddw_count' => $this->categoryCount($filters, $this->ddwCategories()),
            'approved_count' => $this->filteredBeneficiaries($filters)->where(function ($q) {
                $q->where('beneficiaries.approved', true)->orWhere('beneficiaries.approval_status', 'approved');
            })->count(),
            'ongoing_count' => $this->ongoingBeneficiaries($filters)->count(),
            'completed_count' => $this->completedBeneficiaries($filters)->count(),
        ];
    }

    private function applicationReport(array $filters): array
    {
        $base = $this->filteredApplications($filters);

        return [
            'total_applications' => $base->count(),
            'pending' => $this->filteredApplications($filters)->whereIn('applications.status', ['pending', 'applied', 'submitted', 'screening'])->count(),
            'approved' => $this->filteredApplications($filters)->where('applications.status', 'approved')->count(),
            'rejected' => $this->filteredApplications($filters)->where('applications.status', 'rejected')->count(),
            'for_exam' => $this->filteredApplications($filters)->where('applications.status', 'for_exam')->count(),
            'for_interview' => $this->filteredApplications($filters)->where('applications.status', 'for_interview')->count(),
            'contract_signing' => $this->filteredApplications($filters)->whereIn('applications.status', ['contract_signing', 'for_contract'])->count(),
            'deployed' => $this->filteredApplications($filters)->whereIn('applications.status', ['deployed', 'assigned'])->count(),
            'completed' => $this->filteredApplications($filters)->where('applications.status', 'completed')->count(),
        ];
    }

    private function attendanceReport(array $filters): array
    {
        $base = $this->filteredAttendances($filters);

        return [
            'submitted_dtr' => $base->count(),
            'approved_dtr' => $this->filteredAttendances($filters)->where('attendances.status', 'approved')->count(),
            'needs_correction' => $this->filteredAttendances($filters)->whereIn('attendances.status', ['needs_correction', 'correction'])->count(),
            'missing_dtr' => max($this->ongoingBeneficiaries($filters)->count() - $base->distinct('attendances.beneficiary_id')->count('attendances.beneficiary_id'), 0),
            'total_rendered_hours' => round((float) $this->filteredAttendances($filters)->selectRaw('COALESCE(SUM(TIMESTAMPDIFF(MINUTE, time_in, time_out)) / 60, 0) as hours')->value('hours'), 2),
        ];
    }

    private function dailyReport(array $filters): array
    {
        return [
            'submitted_reports' => $this->filteredWorkOutputs($filters)->count(),
            'approved_reports' => $this->filteredWorkOutputs($filters)->where('work_outputs.status', 'approved')->count(),
            'needs_correction' => $this->filteredWorkOutputs($filters)->where('work_outputs.status', 'needs_correction')->count(),
            'resubmitted_reports' => $this->filteredWorkOutputs($filters)->whereNotNull('work_outputs.resubmitted_at')->count(),
        ];
    }

    private function employerParticipationReport(array $filters)
    {
        return $this->companyReport($filters)->map(fn ($row) => [
            'company_name' => $row->company_name,
            'active_employer' => (int) $row->total_beneficiaries > 0,
            'beneficiaries_count' => (int) $row->total_beneficiaries,
            'average_rating' => $row->average_rating ? (float) $row->average_rating : 0,
            'evaluation_count' => (int) $row->evaluation_count,
        ])->values();
    }

    private function beneficiariesPerCompany(array $filters, int $limit = 12): array
    {
        $data = $this->companyReport($filters)->take($limit);
        return ['labels' => $data->pluck('company_name')->all(), 'data' => $data->pluck('total_beneficiaries')->map(fn ($v) => (int) $v)->all()];
    }

    private function beneficiariesPerSchool(array $filters, int $limit = 12): array
    {
        $data = Beneficiary::query()
            ->leftJoin('schools', 'beneficiaries.school_id', '=', 'schools.id')
            ->leftJoin('applications', 'applications.beneficiary_id', '=', 'beneficiaries.id')
            ->selectRaw('COALESCE(schools.name, beneficiaries.school_name, "Unspecified") as label, COUNT(DISTINCT beneficiaries.id) as total')
            ->where(function ($q) {
                $q->where('beneficiaries.approved', true)->orWhere('beneficiaries.approval_status', 'approved')->orWhere('beneficiaries.status', 'approved');
            })
            ->when($filters['school_id'], fn ($q) => $q->where('beneficiaries.school_id', $filters['school_id']))
            ->when($filters['employer_id'], fn ($q) => $q->where('beneficiaries.employer_id', $filters['employer_id']))
            ->when($filters['category'], fn ($q) => $q->whereRaw('LOWER(beneficiaries.category) = ?', [strtolower($filters['category'])]))
            ->when($filters['batch_id'], fn ($q) => $q->where('applications.batch_id', $filters['batch_id']))
            ->groupBy('schools.name', 'beneficiaries.school_name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return ['labels' => $data->pluck('label')->all(), 'data' => $data->pluck('total')->map(fn ($v) => (int) $v)->all()];
    }

    private function beneficiaryCategories(array $filters): array
    {
        $data = $this->filteredBeneficiaries($filters)
            ->selectRaw('COALESCE(NULLIF(beneficiaries.category, ""), "Unspecified") as label, COUNT(*) as total')
            ->groupBy('beneficiaries.category')
            ->get();

        return ['labels' => $data->pluck('label')->all(), 'data' => $data->pluck('total')->map(fn ($v) => (int) $v)->all()];
    }

    private function applicationStatusDistribution(array $filters): array
    {
        $data = $this->filteredApplications($filters)
            ->selectRaw('COALESCE(NULLIF(applications.status, ""), "Unspecified") as label, COUNT(*) as total')
            ->groupBy('applications.status')
            ->orderByDesc('total')
            ->get();

        return ['labels' => $data->pluck('label')->all(), 'data' => $data->pluck('total')->map(fn ($v) => (int) $v)->all()];
    }

    private function applicationsPerMonth(array $filters): array
    {
        return $this->monthlySeries($this->filteredApplications($filters), 'applications.created_at', $filters);
    }

    private function completedPerMonth(array $filters): array
    {
        return $this->monthlySeries($this->completedBeneficiaries($filters), 'beneficiaries.completed_at', $filters);
    }

    private function dtrStatusSummary(array $filters): array
    {
        $data = $this->filteredAttendances($filters)
            ->selectRaw('COALESCE(NULLIF(attendances.status, ""), "Submitted") as label, COUNT(*) as total')
            ->groupBy('attendances.status')
            ->get();

        return ['labels' => $data->pluck('label')->all(), 'data' => $data->pluck('total')->map(fn ($v) => (int) $v)->all()];
    }

    private function dailyReportStatusSummary(array $filters): array
    {
        $data = $this->filteredWorkOutputs($filters)
            ->selectRaw('COALESCE(NULLIF(work_outputs.status, ""), "Submitted") as label, COUNT(*) as total')
            ->groupBy('work_outputs.status')
            ->get();

        return ['labels' => $data->pluck('label')->all(), 'data' => $data->pluck('total')->map(fn ($v) => (int) $v)->all()];
    }

    private function monthlySeries(Builder $query, string $dateColumn, array $filters): array
    {
        $months = $this->months($filters);
        $rows = $query->selectRaw("DATE_FORMAT({$dateColumn}, '%Y-%m') as ym, COUNT(*) as total")
            ->whereNotNull($dateColumn)
            ->groupBy('ym')
            ->pluck('total', 'ym');

        return [
            'labels' => array_map(fn ($ym) => Carbon::parse($ym . '-01')->format('M Y'), $months),
            'data' => array_map(fn ($ym) => (int) ($rows[$ym] ?? 0), $months),
        ];
    }

    private function applicantsBySchoolReport(array $filters, ?int $limit = null)
    {
        $query = Application::query()
            ->leftJoin('beneficiaries', 'applications.beneficiary_id', '=', 'beneficiaries.id')
            ->leftJoin('schools', 'beneficiaries.school_id', '=', 'schools.id')
            ->selectRaw('
                COALESCE(schools.name, beneficiaries.school_name, "Unspecified") as school_name,
                COUNT(DISTINCT applications.id) as total_applicants,
                COUNT(DISTINCT CASE WHEN applications.status = "approved" THEN applications.id END) as total_approved,
                COUNT(DISTINCT CASE WHEN applications.status != "completed" THEN applications.beneficiary_id END) as total_ongoing,
                COUNT(DISTINCT CASE WHEN applications.status = "completed" OR beneficiaries.completed_at IS NOT NULL THEN applications.beneficiary_id END) as total_completed
            ')
            ->when($filters['school_id'], fn ($q) => $q->where('beneficiaries.school_id', $filters['school_id']))
            ->when($filters['employer_id'], fn ($q) => $q->leftJoin('job_listings', 'applications.job_listing_id', '=', 'job_listings.id')->where('job_listings.employer_id', $filters['employer_id']))
            ->when($filters['category'], fn ($q) => $q->whereRaw('LOWER(beneficiaries.category) = ?', [strtolower($filters['category'])]))
            ->when($filters['status'], fn ($q) => $q->where('applications.status', $filters['status']))
            ->when($filters['batch_id'], fn ($q) => $q->where('applications.batch_id', $filters['batch_id']))
            ->when($filters['start_date'] && $filters['end_date'], fn ($q) => $q->whereBetween('applications.created_at', [$filters['start_date'], $filters['end_date']]))
            ->groupBy('schools.name', 'beneficiaries.school_name')
            ->orderByDesc('total_applicants');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    private function filteredApplications(array $filters): Builder
    {
        return Application::query()
            ->leftJoin('beneficiaries', 'applications.beneficiary_id', '=', 'beneficiaries.id')
            ->leftJoin('job_listings', 'applications.job_listing_id', '=', 'job_listings.id')
            ->when($filters['start_date'] && $filters['end_date'], fn ($q) => $q->whereBetween('applications.created_at', [$filters['start_date'], $filters['end_date']]))
            ->when($filters['employer_id'], fn ($q) => $q->where('job_listings.employer_id', $filters['employer_id']))
            ->when($filters['school_id'], fn ($q) => $q->where('beneficiaries.school_id', $filters['school_id']))
            ->when($filters['category'], fn ($q) => $q->whereRaw('LOWER(beneficiaries.category) = ?', [strtolower($filters['category'])]))
            ->when($filters['status'], fn ($q) => $q->where('applications.status', $filters['status']))
            ->when($filters['batch_id'], fn ($q) => $q->where('applications.batch_id', $filters['batch_id']));
    }

    private function filteredBeneficiaries(array $filters): Builder
    {
        return Beneficiary::query()
            ->leftJoin('applications', 'applications.beneficiary_id', '=', 'beneficiaries.id')
            ->when($filters['start_date'] && $filters['end_date'], fn ($q) => $q->whereBetween('beneficiaries.created_at', [$filters['start_date'], $filters['end_date']]))
            ->when($filters['employer_id'], fn ($q) => $q->where('beneficiaries.employer_id', $filters['employer_id']))
            ->when($filters['school_id'], fn ($q) => $q->where('beneficiaries.school_id', $filters['school_id']))
            ->when($filters['category'], fn ($q) => $q->whereRaw('LOWER(beneficiaries.category) = ?', [strtolower($filters['category'])]))
            ->when($filters['batch_id'], fn ($q) => $q->where('applications.batch_id', $filters['batch_id']));
    }

    private function filteredEmployers(array $filters): Builder
    {
        return Employer::query()
            ->when($filters['employer_id'], fn ($q) => $q->where('employers.id', $filters['employer_id']))
            ->where(function ($q) {
                $q->where('approved', true)->orWhere('approval_status', 'approved')->orWhere('status', 'approved')->orWhere('status', 'active');
            });
    }

    private function filteredAttendances(array $filters): Builder
    {
        return Attendance::query()
            ->leftJoin('beneficiaries', 'attendances.beneficiary_id', '=', 'beneficiaries.id')
            ->leftJoin('applications', 'attendances.application_id', '=', 'applications.id')
            ->when($filters['start_date'] && $filters['end_date'], fn ($q) => $q->whereBetween('attendances.date', [$filters['start_date']->toDateString(), $filters['end_date']->toDateString()]))
            ->when($filters['employer_id'], fn ($q) => $q->where('attendances.employer_id', $filters['employer_id']))
            ->when($filters['school_id'], fn ($q) => $q->where('beneficiaries.school_id', $filters['school_id']))
            ->when($filters['category'], fn ($q) => $q->whereRaw('LOWER(beneficiaries.category) = ?', [strtolower($filters['category'])]))
            ->when($filters['batch_id'], fn ($q) => $q->where('applications.batch_id', $filters['batch_id']));
    }

    private function filteredWorkOutputs(array $filters): Builder
    {
        return WorkOutput::query()
            ->leftJoin('beneficiaries', 'work_outputs.beneficiary_id', '=', 'beneficiaries.id')
            ->leftJoin('applications', 'work_outputs.application_id', '=', 'applications.id')
            ->when($filters['start_date'] && $filters['end_date'], fn ($q) => $q->whereBetween('work_outputs.created_at', [$filters['start_date'], $filters['end_date']]))
            ->when($filters['employer_id'], fn ($q) => $q->where('work_outputs.employer_id', $filters['employer_id']))
            ->when($filters['school_id'], fn ($q) => $q->where('beneficiaries.school_id', $filters['school_id']))
            ->when($filters['category'], fn ($q) => $q->whereRaw('LOWER(beneficiaries.category) = ?', [strtolower($filters['category'])]))
            ->when($filters['batch_id'], fn ($q) => $q->where('applications.batch_id', $filters['batch_id']));
    }

    private function ongoingBeneficiaries(array $filters): Builder
    {
        return $this->filteredBeneficiaries($filters)
            ->whereNull('beneficiaries.completed_at')
            ->where(function ($q) {
                $q->where('beneficiaries.employment_status', 'active')
                    ->orWhereIn('beneficiaries.status', ['approved', 'active', 'assigned', 'ongoing'])
                    ->orWhereIn('applications.status', ['approved', 'assigned', 'deployed']);
            });
    }

    private function completedBeneficiaries(array $filters): Builder
    {
        return $this->filteredBeneficiaries($filters)
            ->where(function ($q) {
                $q->whereNotNull('beneficiaries.completed_at')
                    ->orWhere('beneficiaries.employment_status', 'completed')
                    ->orWhere('applications.status', 'completed');
            });
    }

    private function categoryCount(array $filters, array $categories): int
    {
        return $this->filteredBeneficiaries($filters)
            ->whereIn(DB::raw('LOWER(TRIM(beneficiaries.category))'), array_map('strtolower', $categories))
            ->distinct('beneficiaries.id')
            ->count('beneficiaries.id');
    }

    private function ddwCategories(): array
    {
        return [
            'ddw',
            'dependent',
            'dependent_of_displaced_worker',
            'dependent of displaced worker',
            'dependent / displaced worker',
            'displaced_worker',
        ];
    }

    private function filters(Request $request): array
    {
        $start = $request->query('start_date') ?: $request->query('report_start_date');
        $end = $request->query('end_date') ?: $request->query('report_end_date');

        return [
            'start_date' => $start ? Carbon::parse($start)->startOfDay() : null,
            'end_date' => $end ? Carbon::parse($end)->endOfDay() : null,
            'employer_id' => $request->query('employer_id') ?: null,
            'school_id' => $request->query('school_id') ?: null,
            'category' => $request->query('category') ?: null,
            'status' => $request->query('status') ?: null,
            'batch_id' => $request->query('batch_id') ?: null,
        ];
    }

    private function applyDateRange(Builder $query, string $column, array $filters, bool $dateTime = true): void
    {
        if (! $filters['start_date'] || ! $filters['end_date']) {
            return;
        }

        $query->whereBetween($column, $dateTime
            ? [$filters['start_date'], $filters['end_date']]
            : [$filters['start_date']->toDateString(), $filters['end_date']->toDateString()]);
    }

    private function months(array $filters): array
    {
        $start = ($filters['start_date'] ?: now()->copy()->startOfYear())->copy()->startOfMonth();
        $end = ($filters['end_date'] ?: now()->copy()->endOfYear())->copy()->startOfMonth();
        $months = [];

        while ($start->lte($end)) {
            $months[] = $start->format('Y-m');
            $start->addMonth();
        }

        return $months;
    }

    private function emptyReporting(): array
    {
        return [
            'summary' => [],
            'charts' => [],
            'reports' => [],
            'insights' => [],
            'filters' => ['employers' => [], 'schools' => [], 'categories' => [], 'statuses' => [], 'batches' => []],
        ];
    }
}
