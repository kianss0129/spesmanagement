<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\PESO\PESOController;
use App\Http\Controllers\PESO\AnalyticsController;
use App\Http\Controllers\PESO\InterviewController;
use App\Http\Controllers\Employer\EmployerController;
use App\Http\Controllers\Employer\JobController;
use App\Http\Controllers\Beneficiary\BeneficiaryController;
use Illuminate\Foundation\Application;
use Inertia\Inertia;
use App\Http\Controllers\Auth\BeneficiaryRegisterController;
use App\Http\Controllers\Auth\EmployerRegisterController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;

// Guest Routes
Route::middleware('guest')->group(function () {
    
    // Beneficiary registration
    Route::get('register/beneficiary', [BeneficiaryRegisterController::class, 'create'])->name('register.beneficiary');
    Route::post('register/beneficiary', [BeneficiaryRegisterController::class, 'store']);

    // Employer registration
    Route::get('register/employer', [EmployerRegisterController::class, 'create'])->name('register.employer');
    Route::post('register/employer', [EmployerRegisterController::class, 'store']);

    // PESO registration
    Route::get('register/peso', [PESORegisterController::class, 'create'])->name('register.peso');
    Route::post('register/peso', [PESORegisterController::class, 'store']);

    // Forgot Password
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    // Reset Password
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

// Email Verification
Route::middleware('auth')->group(function() {
    Route::get('verify-email', [EmailVerificationPromptController::class,'__invoke'])->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class,'__invoke'])
        ->middleware(['signed','throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class,'store'])
        ->middleware(['throttle:6,1'])->name('verification.send');
});

// Logout
Route::post('logout', [AuthenticatedSessionController::class,'destroy'])->name('logout');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register.patient'),
    'laravelVersion' => Application::VERSION,
    'phpVersion' => PHP_VERSION,
]))->name('home');

// Auth / Dashboard routes (keep your existing auth file)
require __DIR__ . '/auth.php';

// Example protected dashboard route
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return match (auth()->user()->getRoleNames()->first()) {
            'Admin' => Inertia::render('Dashboard/Admin'),
            'Doctor' => Inertia::render('Dashboard/Doctor'),
            'Patient' => Inertia::render('Dashboard/Patient'),
            'Super Admin' => Inertia::render('Dashboard/SuperAdmin'),
            default => abort(403),
        };
    })->name('dashboard');
});

/*
|------------------------------------------
| PESO (role: peso)
|------------------------------------------
*/
Route::middleware(['auth', 'role:peso'])->prefix('peso')->group(function () {
    Route::get('dashboard', [PESOController::class, 'dashboard'])->name('peso.dashboard');

    // Analytics
    Route::get('analytics/applicants-by-school', [AnalyticsController::class, 'applicantsBySchool']);
    Route::get('analytics/top-employers', [AnalyticsController::class, 'topHiringEmployers']);
    Route::get('analytics/performance-trends', [AnalyticsController::class, 'performanceTrends']);

    // Assign beneficiary
    Route::post('assign-beneficiary', [PESOController::class, 'assignBeneficiary']);

    // Schedule interview (PESO)
    Route::post('schedule-interview', [InterviewController::class, 'schedule']);
});

/*
|------------------------------------------
| Employer (role: employer)
|------------------------------------------
*/
Route::middleware(['auth', 'role:employer'])->prefix('employer')->group(function () {
    Route::get('dashboard', [EmployerController::class, 'dashboard'])->name('employer.dashboard');

    // Jobs CRUD


    // View recommended candidates (employer)
    Route::get('recommended-candidates', [EmployerController::class, 'recommendedCandidates']);

    // Schedule interview (employer)
    Route::post('jobs/{id}/interview', [EmployerController::class, 'scheduleInterview']);

    // Rate beneficiary
    Route::post('jobs/{id}/rate/{beneficiary}', [EmployerController::class, 'submitRating']);
});

/*
|------------------------------------------
| Beneficiary (role: beneficiary)
|------------------------------------------
*/
Route::middleware(['auth', 'role:beneficiary'])->prefix('beneficiary')->group(function () {
    Route::get('dashboard', [BeneficiaryController::class, 'dashboard'])->name('beneficiary.dashboard');

    Route::post('applications', [BeneficiaryController::class, 'apply']);
    Route::get('applications', [BeneficiaryController::class, 'listApplications']);
    Route::post('upload-documents', [BeneficiaryController::class, 'uploadDocuments']);
});
