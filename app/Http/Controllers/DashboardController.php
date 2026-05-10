<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\PESO\AnalyticsController;
use App\Http\Controllers\PESO\PESOController;
use App\Http\Controllers\PESO\InterviewController;

use App\Models\User;
use App\Models\Beneficiary;
use App\Models\Employer;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * Unified dashboard for Admin, PESO Admin, and PESO users
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $data = [
            'user' => $user,
        ];

        /*
        |--------------------------------------------------------------------------
        | ADMIN + PESO ADMIN
        |--------------------------------------------------------------------------
        */
        if ($user->hasAnyRole(['Admin', 'PESO Admin'])) {

            $pesoController = new PESOController();
            $analyticsController = new AnalyticsController();
            $adminController = new AdminController();

            $data['beneficiaries'] = $pesoController->monitoring()->getData();
            $data['interviews'] = app(InterviewController::class)->upcoming()->getData();
            $data['jobListings'] = $pesoController->jobListings()->getData();

            $data['completionRates'] = $analyticsController->completionRatePerBatch();
            $data['attendanceCompliance'] = $analyticsController->attendanceCompliance($request);

            $data['applicants'] = $analyticsController->applicantsBySchool($request);
            $data['employers'] = $analyticsController->topHiringEmployers();

            // Full Admin stats
            $data['stats'] = $adminController->getStatsForDashboard();
        }

        /*
        |--------------------------------------------------------------------------
        | PESO USER
        |--------------------------------------------------------------------------
        */
        elseif ($user->hasRole('PESO')) {

            $pesoController = new PESOController();
            $analyticsController = new AnalyticsController();

            $data['beneficiaries'] = $pesoController->monitoring()->getData();
            $data['interviews'] = app(InterviewController::class)->upcoming()->getData();

            $data['applicants'] = $analyticsController->applicantsBySchool($request);
            $data['employers'] = $analyticsController->topHiringEmployers();

            // 🔥 Real-time stats (no AdminController call)
            $data['stats'] = [
                'totalUsers' => User::count(),
                'totalBeneficiaries' => Beneficiary::count(),
                'totalEmployers' => Employer::count(),
                'pesoUsers' => Role::where('name', 'PESO')->first()?->users()->count() ?? 0,
            ];
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

        // Beneficiary
        if ($user->hasRole('Beneficiary')) {
            if (!$user->onboarding_completed) {
                return Redirect::route('onboarding');
            }
            return Redirect::route('dashboard');
        }

        // Admin / PESO
        if ($user->hasAnyRole(['Admin', 'PESO Admin', 'PESO'])) {
            return Redirect::route('dashboard');
        }

        // Employer
        if ($user->hasRole('Employer')) {
            return Redirect::route('employer.dashboard');
        }

        return Redirect::route('login');
    }
}