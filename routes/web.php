<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;

use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;

// =======================
// LOAD MODULAR ROUTES
// =======================
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/beneficiary.php';
require_once __DIR__ . '/employer.php';
require_once __DIR__ . '/peso.php';
require_once __DIR__ . '/admin.php';

// =======================
// HOME & DASHBOARD
// =======================
Route::get('/', [PageController::class, 'welcome'])->name('home');

Route::middleware('auth')->get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('Beneficiary')) {
        $beneficiary = $user->beneficiary;

        if ($beneficiary && $beneficiary->approval_status !== 'approved') {
            return redirect()->route('onboarding.pending');
        }

        return Inertia::render('Beneficiary/Dashboard', [
            'user' => $user,
            'beneficiary' => $beneficiary,
        ]);
    }

    if ($user->hasRole('Employer')) {
        return redirect()->route('employer.dashboard');
    }

    if ($user->hasAnyRole(['Admin', 'PESO Admin', 'PESO'])) {
        return app(DashboardController::class)->index(request());
    }

    return redirect()->route('login');
})->name('dashboard');

Route::middleware('auth')->get('/admin/dashboard', fn () => redirect('/dashboard'));
Route::middleware('auth')->get('/peso/dashboard', fn () => redirect('/dashboard'));

// =======================
// PROFILE
// =======================
Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =======================
// DEV / DEBUG ROUTES
// =======================
Route::prefix('_debug')->middleware('local')->group(function () {
    Route::get('last-reset', fn () => \DB::table('password_resets')->latest('created_at')->first());

    Route::post('ping', function (Request $request) {
        Log::info('debug-ping', [
            'body' => $request->all(),
            'headers' => array_slice(getallheaders(), 0, 20),
        ]);

        return response()->json(['ok' => true, 'received' => $request->all()]);
    });

    Route::get('send-reset', function (Request $request) {
        $email = $request->query('email');
        if (!$email) {
            return response('Provide ?email=you@example.com', 400);
        }

        try {
            $status = Password::sendResetLink(['email' => $email]);
            Log::info('Debug send-reset', ['email' => $email, 'status' => $status]);
            return response()->json(['status' => $status]);
        } catch (\Throwable $e) {
            Log::error('Debug send-reset failed', ['email' => $email, 'exception' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });
});
