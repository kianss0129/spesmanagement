<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\PESO\AnalyticsController;
use App\Http\Controllers\PESO\PESOController;

use App\Models\User;
use App\Models\Beneficiary;
use App\Models\Employer;
use App\Models\Announcement;
use App\Models\Interview;

use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * Unified dashboard for Admin, PESO Admin, and PESO users
     * STRICT role-based data access (no role mixing)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $data = [
            'user' => $user,
        ];

        /*
        |--------------------------------------------------------------------------
        | ADMIN ONLY - FULL ACCESS
        |--------------------------------------------------------------------------
        */
        if ($user->hasRole('Admin')) {

            $pesoController = new PESOController();
            $analyticsController = new AnalyticsController();
            $adminController = new AdminController();

            // Full monitoring
            $data['beneficiaries'] = $pesoController->monitoring()->getData();

            // Interviews / scheduling (Admin only)
            $data['interviews'] = app(\App\Http\Controllers\PESO\InterviewController::class)
                ->upcoming()
                ->getData();

            // Job listings (Admin only)
            $data['jobListings'] = $pesoController->jobListings()->getData();

            // Full Analytics
            $data['applicants'] = $analyticsController->applicantsBySchool($request);
            $data['employers'] = $analyticsController->topHiringEmployers();

            // Performance trends (Admin only)
            $data['performance'] = $analyticsController->performanceTrends($request);

            // Growth analytics (Admin only)
            $data['chartStats'] = $analyticsController->dashboardStats($request);

            // Announcements (read/write)
            $data['announcements'] = Announcement::latest()->take(10)->get();

            // Full Admin stats
            $data['stats'] = $adminController->getStatsForDashboard();
        }

        /*
        |--------------------------------------------------------------------------
        | PESO ADMIN ONLY - LIMITED ADMIN ACCESS
        |--------------------------------------------------------------------------
        */
        elseif ($user->hasRole('PESO Admin')) {

            $pesoController = new PESOController();
            $analyticsController = new AnalyticsController();

            // Full monitoring (limited to PESO scope)
            $data['beneficiaries'] = $pesoController->monitoring()->getData();

            // Interviews / scheduling (PESO Admin can manage)
            $data['interviews'] = app(\App\Http\Controllers\PESO\InterviewController::class)
                ->upcoming()
                ->getData();

            // Job listings (PESO Admin limited view)
            $data['jobListings'] = $pesoController->jobListings()->getData();

            // Limited Analytics
            $data['applicants'] = $analyticsController->applicantsBySchool($request);
            $data['employers'] = $analyticsController->topHiringEmployers();

            // Announcements (read/write)
            $data['announcements'] = Announcement::latest()->take(10)->get();

            // Limited stats
            $pendingBeneficiaries = Beneficiary::where('approval_status', 'pending')->count();
            $pendingEmployers = Employer::where('approved', false)->count();

            $data['stats'] = [
                'totalUsers' => User::count(),
                'totalBeneficiaries' => Beneficiary::count(),
                'totalEmployers' => Employer::count(),
                'pending_applications' => $pendingBeneficiaries + $pendingEmployers,
                'pesoUsers' => Role::where('name', 'PESO')
                    ->first()?->users()->count() ?? 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | PESO USER ONLY - MONITORING ONLY (STRICT READ-ONLY)
        |--------------------------------------------------------------------------
        | No interviews, jobs, contracts, exports, analytics, or sensitive data
        */
        elseif ($user->hasAnyRole(['PESO', 'PESO User'])) {
            $assignedInterviews = Interview::with([
                    'beneficiary.user',
                    'beneficiary.school',
                    'beneficiary.skills',
                    'jobListing.employer',
                    'application',
                    'scheduledBy:id,name,email',
                    'interviewer:id,name,email',
                ])
                ->where('interviewer_id', $user->id)
                ->whereIn('status', ['scheduled', 'completed'])
                ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 ELSE 1 END")
                ->orderBy('scheduled_at')
                ->get()
                ->map(fn (Interview $interview) => app(\App\Http\Controllers\PESO\InterviewController::class)->formatForPesoUser($interview))
                ->values();

            // ✅ ALLOWED: Count-only stats
            $data['stats'] = [
                'assignedInterviews' => $assignedInterviews->count(),
                'upcomingInterviews' => $assignedInterviews->where('status', 'scheduled')->count(),
                'completedInterviews' => $assignedInterviews->where('status', 'completed')->count(),
                'needsReviewInterviews' => $assignedInterviews->where('result', 'needs_review')->count(),
            ];
            $data['assignedInterviews'] = $assignedInterviews;

            // ✅ ALLOWED: Approved beneficiaries list (read-only)
            $data['approvedBeneficiaries'] = Beneficiary::where('status', 'approved')
                ->select([
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'school_id',
                    'program',
                    'approved_at',
                    'status',
                ])
                ->latest()
                ->get();

            // ✅ ALLOWED: Approved employers list (read-only)
            $data['approvedEmployers'] = Employer::where('status', 'approved')
                ->select([
                    'id',
                    'company_name as name',
                    'email',
                    'contact_person',
                    'phone',
                    'approved_at',
                    'approval_status',
                    'status',
                ])
                ->latest()
                ->get();

            // ✅ ALLOWED: Announcements (read-only)
            $data['announcements'] = Announcement::latest()
                ->take(10)
                ->get();

            /*
            |--------------------------------------------------------------------------
            | ❌ STRICTLY REMOVED FOR PESO USER
            |--------------------------------------------------------------------------
            | These are NOT included in the response:
            |
            | ❌ beneficiaries (full list with edit capability)
            | ❌ interviews (no access to interview management)
            | ❌ jobListings (no access to job management)
            | ❌ completionRates (no access to advanced analytics)
            | ❌ attendanceCompliance (no access to attendance analytics)
            | ❌ applicants (no access to applicant analytics)
            | ❌ employers (no access to employer analytics)
            | ❌ performance (no performance trends)
            | ❌ chartStats (no growth/advanced charts)
            | ❌ Full admin stats
            |
            | ACTIONS BLOCKED:
            | ❌ No data export/download
            | ❌ No interview scheduling/modification
            | ❌ No exam flow control
            | ❌ No contract signing management
            | ❌ No role management
            | ❌ No approvals/rejections
            |
            */
        }

        if ($user->hasAnyRole(['PESO', 'PESO User'])) {
            return Inertia::render('PesoUser/Dashboard', $data);
        }

        return Inertia::render('Dashboard', $data);
    }

    /**
     * Smart redirect method
     */
    public function redirect()
    {
        $user = auth()->user();

        if (!$user) {
            return Redirect::route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | Beneficiary
        |--------------------------------------------------------------------------
        */
     if ($user->hasRole('Beneficiary')) {

    if ($user->is_temporary_password) {
        return Redirect::route('password.change');
    }

    if (!$user->onboarding_completed) {
        return Redirect::route('onboarding');
    }

    return Redirect::route('dashboard');
}

        /*
        |--------------------------------------------------------------------------
        | Admin / PESO
        |--------------------------------------------------------------------------
        */
        if ($user->hasAnyRole(['Admin', 'PESO Admin', 'PESO'])) {
            return Redirect::route('dashboard');
        }

        /*
        |--------------------------------------------------------------------------
        | Employer
        |--------------------------------------------------------------------------
        */
        if ($user->hasRole('Employer')) {
            return Redirect::route('employer.dashboard');
        }

        return Redirect::route('login');
    }
}
