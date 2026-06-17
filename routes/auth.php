<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Password;

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
use App\Http\Controllers\Auth\ForgotPasswordOtpController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Beneficiary\OnboardingController;

// =======================
// GUEST ROUTES - Registration & Login
// =======================
Route::middleware('guest')->group(function () {

    // =====================
    // REGISTRATION
    // =====================

    // Beneficiary Registration
    Route::get('register/beneficiary', [BeneficiaryRegisterController::class, 'create'])
        ->name('register.beneficiary');
    Route::post('register/beneficiary', [BeneficiaryRegisterController::class, 'store'])
        ->name('register.beneficiary.store');

    // Employer Registration
    Route::get('register/employer', [EmployerRegisterController::class, 'create'])
        ->name('register.employer');
    Route::post('register/employer', [EmployerRegisterController::class, 'store'])
        ->name('register.employer.store')
        ->middleware('throttle:heavy-actions');

    // PESO Registration
    Route::get('register/peso', [PESORegisterController::class, 'create'])
        ->name('register.peso');
    Route::post('register/peso', [PESORegisterController::class, 'store'])
        ->name('register.peso.store')
        ->middleware('throttle:heavy-actions');

    // =====================
    // PASSWORD RESET (OTP)
    // =====================
    Route::post('/forgot-password-otp', [ForgotPasswordOtpController::class, 'sendOtp'])
        ->name('password.otp.send')
        ->middleware('throttle:5,1');

    Route::post('/reset-password-otp', [ForgotPasswordOtpController::class, 'resetPassword'])
        ->name('password.otp.reset')
        ->middleware('throttle:5,1');

    // =====================
    // LOGIN
    // =====================
    Route::get('login/employer', [PageController::class, 'loginEmployer'])->name('login.employer');
    Route::get('login/peso', [PageController::class, 'loginPeso'])->name('login.peso');

    Route::post('/login', [LoginController::class, 'login'])
        ->name('login')
        ->middleware('throttle:5,1');
});

// =======================
// LOGOUT (Authenticated)
// =======================
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// =======================
// EMAIL VERIFICATION
// =======================
Route::middleware('auth')->group(function () {

    // Show verification notice (Inertia page)
    Route::get('/email/verify', function () {
        return Inertia::render('Auth/VerifyEmail', [
            'status' => session('status')
        ]);
    })->name('verification.notice');

    // Handle verification link click
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        // Redirect based on user type
        $user = $request->user();
        if ($user->hasRole('Employer')) {
            return redirect()->route('employer.dashboard')
                ->with('success', 'Email verified successfully!');
        } elseif ($user->hasRole('Beneficiary')) {
            return redirect()->route('beneficiary.dashboard')
                ->with('success', 'Email verified successfully!');
        }

        return redirect()->route('dashboard')->with('success', 'Email verified successfully!');
    })
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

    // Resend verification email
    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('status', 'already-verified');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })
    ->middleware(['throttle:6,1'])
    ->name('verification.send');
});

// =======================
// ONBOARDING ROUTES
// =======================
Route::middleware(['auth', 'verified'])->prefix('onboarding')->group(function () {
    // Render the SPES onboarding page
    Route::get('/', [OnboardingController::class, 'index'])->name('onboarding');

    // Single submit endpoint — SPES front-end posts the full form (including files)
    Route::post('/submit', [OnboardingController::class, 'submit'])
        ->name('onboarding.submit')
        ->middleware('throttle:heavy-actions');
    // Legacy single-file upload endpoint (used by Upload page)
    Route::post('/upload', [OnboardingController::class, 'upload'])
        ->middleware('throttle:heavy-actions');

    // Keep pending redirect for backward compatibility
    Route::get('/pending', function () {
        return redirect()->route('beneficiary.dashboard');
    })->name('onboarding.pending');
});
