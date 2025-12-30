<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

// Auth Controllers
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

    // REGISTER
    Route::get('register/beneficiary', [BeneficiaryRegisterController::class, 'create'])
        ->name('register.beneficiary');
    Route::post('register/beneficiary', [BeneficiaryRegisterController::class, 'store'])
        ->name('register.beneficiary.store');

    Route::get('register/employer', [EmployerRegisterController::class, 'create'])
        ->name('register.employer');
    Route::post('register/employer', [EmployerRegisterController::class, 'store'])
        ->name('register.employer.store');

    Route::get('register/peso', [PESORegisterController::class, 'create'])
        ->name('register.peso');
    Route::post('register/peso', [PESORegisterController::class, 'store'])
        ->name('register.peso.store');

    // LOGIN
    Route::get('login/employer', [PageController::class, 'loginEmployer'])->name('login.employer');
    Route::get('login/peso', [PageController::class, 'loginPeso'])->name('login.peso');
});

/*
|--------------------------------------------------------------------------
| HOME & DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('/', [PageController::class, 'welcome'])->name('home');

Route::middleware(['auth'])->get('/dashboard', [DashboardController::class, 'redirect'])
    ->name('dashboard');

// Profile management
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
Route::middleware(['auth', 'role:PESO'])->prefix('peso')->name('peso.')->group(function () {

    // Dashboard
    Route::get('dashboard', [PESOController::class, 'dashboard'])->name('dashboard');

    // Analytics
    Route::get('analytics/applicants-by-school', [AnalyticsController::class, 'applicantsBySchool'])
        ->name('analytics.applicantsBySchool');
    Route::get('analytics/top-employers', [AnalyticsController::class, 'topHiringEmployers'])
        ->name('analytics.topEmployers');
    Route::get('analytics/performance-trends', [AnalyticsController::class, 'performanceTrends'])
        ->name('analytics.performanceTrends');
    Route::get('analytics/completion-rate', [AnalyticsController::class, 'completionRatePerBatch'])
        ->name('analytics.completionRate');
    Route::get('analytics/attendance-compliance', [AnalyticsController::class, 'attendanceCompliance'])
        ->name('analytics.attendanceCompliance');

    // Beneficiary assignment & interviews
    Route::post('assign-beneficiary', [PESOController::class, 'assignBeneficiary'])->name('assignBeneficiary');
    Route::post('schedule-interview', [InterviewController::class, 'schedule'])->name('scheduleInterview');

    // DOLE Reports
    Route::get('reports/dole', [PESOController::class, 'exportDOLEReport'])->name('reports.dole');

    // Beneficiary work history
    Route::get('beneficiaries/{id}/work-history', [BeneficiaryController::class, 'workHistory'])
        ->name('beneficiary.workHistory');
});

/*
|--------------------------------------------------------------------------
| EMPLOYER ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Employer'])->prefix('employer')->name('employer.')->group(function () {

    Route::get('dashboard', [EmployerController::class, 'dashboard'])->name('dashboard');

    // Job management
    Route::resource('jobs', JobController::class);
    Route::post('jobs/{id}/interview', [EmployerController::class, 'scheduleInterview'])
        ->name('jobs.scheduleInterview');
    Route::post('jobs/{id}/rate/{beneficiary}', [EmployerController::class, 'submitRating'])
        ->name('jobs.submitRating');

    // Pages
    Route::get('applicants/page', [PageController::class, 'employerApplicants'])->name('page.applicants');
    Route::get('recommended/page', [PageController::class, 'employerRecommended'])->name('page.recommended');
    Route::get('interviews/page', [PageController::class, 'employerInterviews'])->name('page.interviews');
    Route::get('performance/page', [PageController::class, 'employerPerformance'])->name('page.performance');
    Route::get('work-output/page', [PageController::class, 'employerWorkOutput'])->name('page.workOutput');
    Route::get('reports/page', [PageController::class, 'employerReports'])->name('page.reports');
    Route::get('attendance/page', [PageController::class, 'employerAttendance'])->name('page.attendance');

    // API
    Route::get('jobs/{id}/applicants', [EmployerController::class, 'applicants'])->name('jobs.applicants');

    // Dashboard stats and exports
    Route::get('stats', [EmployerController::class, 'stats'])->name('stats');
    Route::get('jobs/{id}/export-applicants', [EmployerController::class, 'exportApplicants'])->name('jobs.exportApplicants');
    Route::get('applicants/{id}/ratings', [EmployerController::class, 'applicantRatings'])->name('applicant.ratings');
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
Route::middleware(['auth', 'role:Beneficiary'])->prefix('beneficiary')->name('beneficiary.')->group(function () {

    Route::get('dashboard', [BeneficiaryController::class, 'dashboard'])->name('dashboard');
    Route::get('profile', [BeneficiaryController::class, 'profilePage'])->name('profile');

    // Applications
    Route::post('applications', [BeneficiaryController::class, 'apply'])->name('applications.store');
    Route::get('applications', [BeneficiaryController::class, 'listApplications'])->name('applications.index');
    Route::get('applications/page', [PageController::class, 'beneficiaryApplications'])->name('page.applications');

    // Jobs
    Route::get('jobs', [BeneficiaryController::class, 'jobs'])->name('jobs');
    Route::get('jobs/page', [PageController::class, 'beneficiaryJobs'])->name('page.jobs');

    // Documents
    Route::get('upload-documents/page', [PageController::class, 'beneficiaryUploadDocuments'])->name('page.uploadDocuments');
    Route::post('upload-documents', [BeneficiaryController::class, 'uploadDocs'])->name('uploadDocuments');

    // Interviews & Attendance
    Route::get('upcoming-interviews', [BeneficiaryController::class, 'interviews'])->name('interviews');
    Route::get('interviews/{id}', [BeneficiaryController::class, 'viewInterview'])->name('interviews.view');
    Route::get('analytics/attendance', [BeneficiaryController::class, 'attendance'])->name('analytics.attendance');
    Route::post('attendance', [BeneficiaryController::class, 'submitAttendance'])->name('attendance.submit');

    // Ratings
    Route::get('ratings', [BeneficiaryController::class, 'getRatings'])->name('ratings');

    // Self work history
    Route::get('work-history', [BeneficiaryController::class, 'myWorkHistory'])->name('beneficiary.workHistory');
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('stats', [AdminController::class, 'stats'])->name('stats');
    Route::get('export-users', [AdminController::class, 'exportUsers'])->name('export.users');

    // Roles management
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('roles/assign', [RoleController::class, 'assign'])->name('roles.assign');
    Route::delete('roles/{user}', [RoleController::class, 'remove'])->name('roles.remove');
});

/*
|--------------------------------------------------------------------------
| DEV / DEBUG ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/_debug/last-reset', function () {
    if (!app()->isLocal()) abort(404);
    return \DB::table('password_resets')->latest('created_at')->first();
});

Route::post('/_debug/ping', function (Request $request) {
    if (!app()->isLocal()) abort(404);
    Log::info('debug-ping', [
        'body' => $request->all(),
        'headers' => array_slice(getallheaders(), 0, 20)
    ]);
    return response()->json(['ok' => true, 'received' => $request->all()]);
});
