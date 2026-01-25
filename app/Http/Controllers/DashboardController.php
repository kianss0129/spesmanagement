<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\PESO\AnalyticsController;
use App\Http\Controllers\PESO\PESOController;

class DashboardController extends Controller
{
    /**
     * Unified dashboard for Admin, PESO Admin, and PESO users
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Prepare common data
        $data = [
            'user' => $user,
        ];

        // Beneficiaries for monitoring (visible to Admin, PESO Admin, PESO)
        if ($user->hasAnyRole(['Admin', 'PESO Admin', 'PESO'])) {
            $pesoController = new PESOController();
            $data['beneficiaries'] = $pesoController->monitoring()->getData();
            $interviewController = new \App\Http\Controllers\PESO\InterviewController();
            $data['interviews'] = $interviewController->upcoming()->getData();
            // Job listings for management (Admin, PESO Admin)
            if ($user->hasAnyRole(['Admin', 'PESO Admin'])) {
                $data['jobListings'] = $pesoController->jobListings()->getData();
                // Analytics data
                $analyticsController = new AnalyticsController();
                $data['completionRates'] = $analyticsController->completionRatePerBatch();
                $data['attendanceCompliance'] = $analyticsController->attendanceCompliance($request);
            }
        }

        // Role-based data
        if ($user->hasRole('Admin') || $user->hasRole('PESO Admin')) {
            // Full admin data
            $adminController = new AdminController();
            $adminResponse = $adminController->dashboard();
            // Since it returns Inertia response, we need to get the data differently
            // For now, call the method that provides data
            $data['stats'] = $adminController->getStatsForDashboard();
        } elseif ($user->hasRole('PESO')) {
            // PESO data
            $analyticsController = new AnalyticsController();
            $data['applicants'] = $analyticsController->applicantsBySchool($request);
            $data['employers'] = $analyticsController->topHiringEmployers();
            $data['totals'] = [
                'applications' => 0, // Would need to calculate
                'assigned' => 0,
                'interviews' => 0,
                'attendance' => 0,
            ];
            $data['stats'] = []; // Minimal stats for PESO
        }

        return Inertia::render('Dashboard', $data);
    }

    /**
     * Legacy redirect method for backward compatibility
     */
    public function redirect()
    {
        $user = auth()->user();

        // If no user is logged in, send to login page
        if (!$user) {
            return Redirect::route('login');
        }

        // Beneficiary onboarding check
        if ($user->hasRole('Beneficiary')) {
            if (!$user->onboarding_completed) {
                return Redirect::route('onboarding');
            }
            return Redirect::route('onboarding'); // fallback
        }

        // Admin/PESO roles go to unified dashboard
        if ($user->hasRole('Admin') || $user->hasRole('PESO Admin') || $user->hasRole('PESO')) {
            return Redirect::route('dashboard');
        }

        // Employer goes to their dashboard
        if ($user->hasRole('Employer')) {
            return Redirect::route('employer.dashboard');
        }

        // fallback
        return Redirect::route('login');
    }
}
