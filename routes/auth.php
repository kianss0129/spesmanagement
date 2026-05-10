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
        ->name('register.employer.store');

    // PESO Registration
    Route::get('register/peso', [PESORegisterController::class, 'create'])
        ->name('register.peso');
    Route::post('register/peso', [PESORegisterController::class, 'store'])
        ->name('register.peso.store');

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
            return redirect()->route('onboarding', ['category' => 'employer'])
                ->with('success', 'Email verified successfully!');
        } elseif ($user->hasRole('Beneficiary')) {
            return redirect()->route('onboarding', ['category' => $user->beneficiary_type])
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
    Route::get('/', [OnboardingController::class, 'index'])->name('onboarding');

    Route::post('/step1', [OnboardingController::class, 'step1']);
    Route::post('/step2', [OnboardingController::class, 'step2']);
    Route::post('/upload', [OnboardingController::class, 'upload']);

    Route::post('/submit', [OnboardingController::class, 'submit'])->name('onboarding.submit');

    Route::get('/pending', function () {
        $user = auth()->user();

        if ($user && $user->hasRole('Employer')) {
            $employer = $user->employer;
            if ($employer && $employer->approval_status === 'approved') {
                return redirect()->route('employer.dashboard');
            }
        }

        if ($user && $user->hasRole('Beneficiary')) {
            $beneficiary = $user->beneficiary;
            if ($beneficiary && $beneficiary->approval_status === 'approved') {
                return redirect()->route('dashboard');
            }
        }

        return Inertia::render('Onboarding/Pending');
    })->name('onboarding.pending');
});
