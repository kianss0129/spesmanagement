<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Application;
use Inertia\Inertia;

// Controllers
use App\Http\Controllers\PageController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\PESO\PESOController;
use App\Http\Controllers\PESO\AnalyticsController;
use App\Http\Controllers\PESO\InterviewController;
use App\Http\Controllers\Employer\EmployerController;
use App\Http\Controllers\Employer\JobController;
use App\Http\Controllers\Beneficiary\BeneficiaryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;

// Auth controllers 
use App\Http\Controllers\Auth\BeneficiaryRegisterController;
use App\Http\Controllers\Auth\EmployerRegisterController;
use App\Http\Controllers\Auth\PESORegisterController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;

/*
|--------------------------------------------------------------------------
| GUEST ROUTES (LOGIN + REGISTER)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    // (temporary debug route removed from guest group to avoid route collision)

    // ================= REGISTER =================
    Route::get('register/beneficiary', [BeneficiaryRegisterController::class, 'create'])
        ->name('register.beneficiary');
    Route::post('register/beneficiary', [BeneficiaryRegisterController::class, 'store'])->name('register.beneficiary.store');

    Route::get('register/employer', [EmployerRegisterController::class, 'create'])
        ->name('register.employer');
    Route::post('register/employer', [EmployerRegisterController::class, 'store'])->name('register.employer.store');

    Route::get('register/peso', [PESORegisterController::class, 'create'])
        ->name('register.peso');
    Route::post('register/peso', [PESORegisterController::class, 'store'])->name('register.peso.store');

    // ================= LOGIN =================
    // The main login/password routes are defined in routes/auth.php
    // Keep the role-specific pages served by controller methods for route caching.

    Route::get('login/employer', [PageController::class, 'loginEmployer'])
        ->name('login.employer');

    Route::get('login/peso', [PageController::class, 'loginPeso'])
        ->name('login.peso');
});


/* Email verification, password and logout routes are defined in routes/auth.php */

/*
|--------------------------------------------------------------------------
| HOME & DASHBOARD REDIRECT
|--------------------------------------------------------------------------
*/


Route::get('/', [PageController::class, 'welcome'])->name('home');

Route::middleware(['auth'])->get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');

// Generic profile management (for all authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| PESO ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:PESO'])->prefix('peso')->group(function () {
    Route::get('dashboard', [PESOController::class, 'dashboard'])->name('peso.dashboard');

    Route::get('analytics/applicants-by-school', [AnalyticsController::class, 'applicantsBySchool'])->name('peso.analytics.applicantsBySchool');
    Route::get('analytics/top-employers', [AnalyticsController::class, 'topHiringEmployers'])->name('peso.analytics.topEmployers');
    Route::get('analytics/performance-trends', [AnalyticsController::class, 'performanceTrends'])->name('peso.analytics.performanceTrends');

    Route::post('assign-beneficiary', [PESOController::class, 'assignBeneficiary'])->name('peso.assignBeneficiary');
    Route::post('schedule-interview', [InterviewController::class, 'schedule'])->name('peso.scheduleInterview');
});

/*
|--------------------------------------------------------------------------
| EMPLOYER ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Employer'])->prefix('employer')->name('employer.')->group(function () {
    Route::get('dashboard', [EmployerController::class, 'dashboard'])->name('dashboard');

    Route::get('recommended-candidates', [EmployerController::class, 'recommendedCandidates'])->name('recommendedCandidates');
    Route::get('analytics/applicants-per-job', [EmployerController::class, 'applicantsPerJob'])->name('analytics.applicantsPerJob');
    Route::post('jobs/{id}/interview', [EmployerController::class, 'scheduleInterview'])->name('jobs.scheduleInterview');
    Route::post('jobs/{id}/rate/{beneficiary}', [EmployerController::class, 'submitRating'])->name('jobs.submitRating');

    Route::resource('jobs', JobController::class);
    // Employer pages
    Route::get('applicants/page', [PageController::class, 'employerApplicants'])->name('page.applicants');
    Route::get('recommended/page', [PageController::class, 'employerRecommended'])->name('page.recommended');
    Route::get('interviews/page', [PageController::class, 'employerInterviews'])->name('page.interviews');
    Route::get('performance/page', [PageController::class, 'employerPerformance'])->name('page.performance');
    Route::get('work-output/page', [PageController::class, 'employerWorkOutput'])->name('page.workOutput');
    Route::get('reports/page', [PageController::class, 'employerReports'])->name('page.reports');
    Route::get('attendance/page', [PageController::class, 'employerAttendance'])->name('page.attendance');

    // Employer API endpoints
    Route::get('jobs/{id}/applicants', [EmployerController::class, 'applicants'])->name('jobs.applicants');
    Route::get('applicants/{id}/ratings', [EmployerController::class, 'applicantRatings'])->name('applicant.ratings');
    Route::post('jobs/{id}/choose/{applicationId}', [EmployerController::class, 'chooseApplicant'])->name('jobs.chooseApplicant');
    Route::get('interviews', [EmployerController::class, 'interviews'])->name('interviews');
    Route::get('attendance', [EmployerController::class, 'listAttendance'])->name('attendance.list');
    Route::post('attendance/mark', [EmployerController::class, 'submitAttendance'])->name('attendance.mark');
    Route::post('work-output', [EmployerController::class, 'submitWorkOutput'])->name('workOutput.submit');
    Route::post('reports', [EmployerController::class, 'submitReport'])->name('reports.submit');
});

/*
|-------------------------------------------------------------------------- 
| BENEFICIARY ROUTES
|-------------------------------------------------------------------------- 
*/

Route::middleware(['auth', 'role:Beneficiary'])->prefix('beneficiary')->group(function () {

    // Dashboard
    Route::get('dashboard', [BeneficiaryController::class, 'dashboard'])
        ->name('beneficiary.dashboard');

    // Profile page
    Route::get('profile', [BeneficiaryController::class, 'profilePage'])
        ->name('beneficiary.profile');

    // Applications
    Route::post('applications', [BeneficiaryController::class, 'apply'])->name('beneficiary.applications.store');
    Route::get('applications', [BeneficiaryController::class, 'listApplications'])->name('beneficiary.applications.index');
    // Rendered pages
    Route::get('applications/page', [PageController::class, 'beneficiaryApplications'])->name('beneficiary.page.applications');
    Route::get('upload-documents/page', [PageController::class, 'beneficiaryUploadDocuments'])->name('beneficiary.page.uploadDocuments');
    Route::get('jobs/page', [PageController::class, 'beneficiaryJobs'])->name('beneficiary.page.jobs');
    // JSON endpoint for job listings
    Route::get('jobs', [BeneficiaryController::class, 'jobs'])->name('beneficiary.jobs');
    // Upload documents (controller handles logging & validation; logs only in local)
    Route::post('upload-documents', [BeneficiaryController::class, 'uploadDocs'])->name('beneficiary.uploadDocuments');

    // Upcoming Interviews
    Route::get('upcoming-interviews', [BeneficiaryController::class, 'interviews'])->name('beneficiary.interviews');

    // Attendance Analytics (for Chart)
    Route::get('analytics/attendance', [BeneficiaryController::class, 'attendance'])->name('beneficiary.analytics.attendance');
    // Submit attendance (simple endpoint)
    Route::post('attendance', [BeneficiaryController::class, 'submitAttendance'])->name('beneficiary.attendance.submit');
    // Ratings for beneficiary (use web session auth)
    Route::get('ratings', [BeneficiaryController::class, 'getRatings'])->name('beneficiary.ratings');
});


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('stats', [AdminController::class, 'stats'])->name('admin.stats');


    // Roles management (Admin)
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('roles/assign', [RoleController::class, 'assign'])->name('roles.assign');
    Route::delete('roles/{user}', [RoleController::class, 'remove'])->name('roles.remove');
});

// DEV: simple debug route to inspect the latest password reset token (local only)
Route::get('/_debug/last-reset', function () {
    if (!app()->isLocal()) {
        abort(404);
    }

    return \Illuminate\Support\Facades\DB::table('password_resets')->latest('created_at')->first();
});

// DEV: simple POST ping endpoint to test whether POST requests reach the application
Route::post('/_debug/ping', function (\Illuminate\Http\Request $request) {
    if (!app()->isLocal()) {
        abort(404);
    }

    \Illuminate\Support\Facades\Log::info('debug-ping', ['body' => $request->all(), 'headers' => array_slice(getallheaders(), 0, 20)]);
    return response()->json(['ok' => true, 'received' => $request->all()]);
});
