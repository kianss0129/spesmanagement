<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Password;

// =======================
// Controllers
// =======================
use App\Http\Controllers\PageController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\PESO\PESOController;
use App\Http\Controllers\PESO\AnalyticsController;
use App\Http\Controllers\PESO\InterviewController;
use App\Http\Controllers\Employer\EmployerController;
use App\Http\Controllers\Employer\JobController;
use App\Http\Controllers\Beneficiary\BeneficiaryController;
use App\Http\Controllers\Beneficiary\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UploadDocumentsController;

// =======================
// Auth Controllers
// =======================
use App\Http\Controllers\Auth\{
    BeneficiaryRegisterController,
    EmployerRegisterController,
    PESORegisterController,
    AuthenticatedSessionController,
    PasswordResetLinkController,
    NewPasswordController,
    EmailVerificationPromptController,
    VerifyEmailController,
    EmailVerificationNotificationController,
    LoginController
};

// =======================
// GUEST ROUTES
// =======================
Route::middleware('guest')->group(function () {
    // Registration
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

    // Login Pages
    Route::get('login/employer', [PageController::class, 'loginEmployer'])->name('login.employer');
    Route::get('login/peso', [PageController::class, 'loginPeso'])->name('login.peso');

    // Login Submit
    Route::post('/login', [LoginController::class, 'login'])
        ->name('login')
        ->middleware('throttle:5,1');
});

// =======================
// LOGOUT
// =======================
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// =======================
// ONBOARDING ROUTES
// =======================
Route::middleware(['auth'])->prefix('onboarding')->group(function () {
    Route::get('/', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/step1', [OnboardingController::class, 'step1']);
    Route::post('/step2', [OnboardingController::class, 'step2']);
    Route::post('/upload', [OnboardingController::class, 'upload']);
    Route::post('/submit', [OnboardingController::class, 'submit']);
    Route::get('/pending', function () {
        return Inertia::render('Onboarding/Pending');
    })->name('onboarding.pending');
});

// =======================
// HOME & DASHBOARD
// =======================
Route::get('/', [PageController::class, 'welcome'])->name('home');
Route::middleware(['auth'])->get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');

// =======================
// PROFILE
// =======================
Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =======================
// PESO / Admin Routes
// =======================
Route::middleware(['auth', 'role:PESO|Admin'])->prefix('peso')->name('peso.')->group(function () {

    // Dashboard
    Route::get('dashboard', [PESOController::class, 'dashboard'])->name('dashboard');

    // Pending Beneficiaries - PESO or Admin
    Route::get('beneficiaries/pending', [PESOController::class, 'pendingBeneficiaries'])
        ->name('beneficiaries.pending');

         // Pending Employers
    Route::get('employers/pending', [PESOController::class, 'pendingEmployers'])
        ->name('employers.pending');

    // Approve/Reject - Only PESO Admin
    Route::middleware(['role:PESO Admin'])->group(function () {
        Route::post('beneficiaries/{id}/approve', [PESOController::class, 'approveBeneficiary'])
            ->name('beneficiaries.approve');
        Route::post('beneficiaries/{id}/reject', [PESOController::class, 'rejectBeneficiary'])
            ->name('beneficiaries.reject');
    });

    // Analytics
    Route::prefix('analytics')->group(function () {
        Route::get('applicants-by-school', [AnalyticsController::class, 'applicantsBySchool'])
            ->name('analytics.applicantsBySchool');
        Route::get('top-employers', [AnalyticsController::class, 'topHiringEmployers'])
            ->name('analytics.topEmployers');
        Route::get('performance-trends', [AnalyticsController::class, 'performanceTrends'])
            ->name('analytics.performanceTrends');
        Route::get('completion-rate', [AnalyticsController::class, 'completionRatePerBatch'])
            ->name('analytics.completionRate');
        Route::get('attendance-compliance', [AnalyticsController::class, 'attendanceCompliance'])
            ->name('analytics.attendanceCompliance');
    });

    // Actions
    Route::post('assign-beneficiary', [PESOController::class, 'assignBeneficiary'])->name('assignBeneficiary');
    Route::post('schedule-interview', [InterviewController::class, 'schedule'])->name('scheduleInterview');
    Route::get('reports/dole', [PESOController::class, 'exportDOLEReport'])->name('reports.dole');

    // Work History
    Route::get('beneficiaries/{id}/work-history', [BeneficiaryController::class, 'workHistory'])
        ->name('beneficiary.workHistory');

});


    // Pending Beneficiaries
    Route::get('beneficiaries/pending', [PESOController::class, 'pendingBeneficiaries'])
        ->name('beneficiaries.pending');

    // Approve/Reject — PESO Admin Only
    Route::middleware(['role:PESO Admin'])->group(function () {
        Route::post('beneficiaries/{id}/approve', [PESOController::class, 'approveBeneficiary'])
            ->name('beneficiaries.approve');
        Route::post('beneficiaries/{id}/reject', [PESOController::class, 'rejectBeneficiary'])
            ->name('beneficiaries.reject');
    });

// =======================
// EMPLOYER ROUTES
// =======================
Route::middleware(['auth', 'role:Employer'])->prefix('employer')->name('employer.')->group(function () {
    Route::get('dashboard', [EmployerController::class, 'dashboard'])->name('dashboard');

    Route::resource('jobs', JobController::class);
    Route::post('jobs/{id}/interview', [EmployerController::class, 'scheduleInterview'])->name('jobs.scheduleInterview');
    Route::post('jobs/{id}/rate/{beneficiary}', [EmployerController::class, 'submitRating'])->name('jobs.submitRating');

    Route::get('applicants/page', [PageController::class, 'employerApplicants'])->name('page.applicants');
    Route::get('recommended/page', [PageController::class, 'employerRecommended'])->name('page.recommended');
    Route::get('interviews/page', [PageController::class, 'employerInterviews'])->name('page.interviews');
    Route::get('performance/page', [PageController::class, 'employerPerformance'])->name('page.performance');
    Route::get('work-output/page', [PageController::class, 'employerWorkOutput'])->name('page.workOutput');
    Route::get('reports/page', [PageController::class, 'employerReports'])->name('page.reports');
    Route::get('attendance/page', [PageController::class, 'employerAttendance'])->name('page.attendance');

    Route::get('jobs/{id}/applicants', [EmployerController::class, 'applicants'])->name('jobs.applicants');
});

// =======================
// ADMIN ROUTES
// =======================
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('stats', [AdminController::class, 'stats'])->name('stats');
    Route::get('export-users', [AdminController::class, 'exportUsers'])->name('export.users');

    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('roles/assign', [RoleController::class, 'assign'])->name('roles.assign');
    Route::delete('roles/{user}', [RoleController::class, 'remove'])->name('roles.remove');
});

// =======================
// DEV / DEBUG ROUTES
// =======================
Route::prefix('_debug')->middleware('local')->group(function () {
    Route::get('last-reset', function () {
        return \DB::table('password_resets')->latest('created_at')->first();
    });

    Route::post('ping', function (Request $request) {
        Log::info('debug-ping', [
            'body' => $request->all(),
            'headers' => array_slice(getallheaders(), 0, 20)
        ]);
        return response()->json(['ok' => true, 'received' => $request->all()]);
    });

    Route::get('send-reset', function (Request $request) {
        $email = $request->query('email');
        if (!$email) return response('Provide ?email=you@example.com', 400);

        try {
            $status = Password::sendResetLink(['email' => $email]);
            Log::info('Debug send-reset', ['email' => $email, 'status' => $status]);
            return response()->json(['status' => $status]);
        } catch (\Throwable $e) {
            Log::error('Debug send-reset failed', ['email' => $email, 'exception' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

    Route::get('send-test-mail', function (Request $request) {
        $email = $request->query('email');
        if (!$email) return response('Provide ?email=you@example.com', 400);

        try {
            \Illuminate\Support\Facades\Mail::raw('Test email from SPES system', function ($m) use ($email) {
                $m->to($email)->subject('SPES Test Email');
            });
            Log::info('Debug send-test-mail sent', ['email' => $email]);
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Debug send-test-mail exception', ['email' => $email, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    });
});
