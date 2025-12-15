<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Inertia\Inertia;

// Controllers
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\PESO\PESOController;
use App\Http\Controllers\PESO\AnalyticsController;
use App\Http\Controllers\PESO\InterviewController;
use App\Http\Controllers\Employer\EmployerController;
use App\Http\Controllers\Employer\JobController;
use App\Http\Controllers\Beneficiary\BeneficiaryController;

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

    Route::get('register/beneficiary', [BeneficiaryRegisterController::class, 'create'])
        ->name('register.beneficiary');
    Route::post('register/beneficiary', [BeneficiaryRegisterController::class, 'store']);

    Route::get('register/employer', [EmployerRegisterController::class, 'create'])
        ->name('register.employer');
    Route::post('register/employer', [EmployerRegisterController::class, 'store']);

    Route::get('register/peso', [PESORegisterController::class, 'create'])
        ->name('register.peso');
    Route::post('register/peso', [PESORegisterController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| EMAIL VERIFICATION & AUTH-ONLY ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function() {
    Route::get('verify-email', [EmailVerificationPromptController::class,'__invoke'])
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class,'__invoke'])
        ->middleware(['signed','throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class,'store'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');

    Route::post('logout', [AuthenticatedSessionController::class,'destroy'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| HOME & DASHBOARD REDIRECT
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => true,
    'laravelVersion' => Application::VERSION,
    'phpVersion' => PHP_VERSION,
]))->name('home');

Route::middleware(['auth'])->get('/dashboard', function () {
    return match (auth()->user()->role) {
        'Admin' => redirect()->route('admin.dashboard'),
        'PESO' => redirect()->route('peso.dashboard'),
        'Employer' => redirect()->route('employer.dashboard'),
        'Beneficiary' => redirect()->route('beneficiary.dashboard'),
        default => abort(403),
    };
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| PESO ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:PESO'])->prefix('peso')->group(function () {
    Route::get('dashboard', [PESOController::class, 'dashboard'])->name('peso.dashboard');

    Route::get('analytics/applicants-by-school', [AnalyticsController::class, 'applicantsBySchool']);
    Route::get('analytics/top-employers', [AnalyticsController::class, 'topHiringEmployers']);
    Route::get('analytics/performance-trends', [AnalyticsController::class, 'performanceTrends']);

    Route::post('assign-beneficiary', [PESOController::class, 'assignBeneficiary']);
    Route::post('schedule-interview', [InterviewController::class, 'schedule']);
});

/*
|--------------------------------------------------------------------------
| EMPLOYER ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Employer'])->prefix('employer')->group(function () {
    Route::get('dashboard', [EmployerController::class, 'dashboard'])->name('employer.dashboard');

    Route::get('recommended-candidates', [EmployerController::class, 'recommendedCandidates']);
    Route::post('jobs/{id}/interview', [EmployerController::class, 'scheduleInterview']);
    Route::post('jobs/{id}/rate/{beneficiary}', [EmployerController::class, 'submitRating']);

    Route::resource('jobs', JobController::class);
});

/*
|-------------------------------------------------------------------------- 
| BENEFICIARY ROUTES
|-------------------------------------------------------------------------- 
*/
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth', 'role:Beneficiary'])->prefix('beneficiary')->group(function () {

    // Dashboard
    Route::get('dashboard', [BeneficiaryController::class, 'dashboard'])
        ->name('beneficiary.dashboard');

    // Applications
    Route::post('applications', [BeneficiaryController::class, 'apply']);
    Route::get('applications', [BeneficiaryController::class, 'listApplications']);
    Route::post('upload-documents', [BeneficiaryController::class, 'uploadDocs']);

    // Upcoming Interviews
    Route::get('upcoming-interviews', [BeneficiaryController::class, 'interviews']);

    // Attendance Analytics (for Chart)
    Route::get('analytics/attendance', [BeneficiaryController::class, 'attendance']);
});


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});
