<?php
use App\Http\Controllers\GoogleController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
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


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');



Route::get('/manual', function () {
    return Inertia::render('Manual');
})->name('manual');

Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');





// =======================
// GOOGLE OAUTH ROUTES
// =======================
Route::middleware('guest')->get('/auth/google', [GoogleController::class, 'redirect'])
    ->name('google.login');

Route::get('/auth/google/callback', [GoogleController::class, 'callback'])
    ->name('google.callback');


// =======================
// HOME & DASHBOARD
// =======================
Route::get('/', [PageController::class, 'welcome'])->name('home');

Route::middleware('auth')->get('/dashboard', function () {

    $user = auth()->user();

    if ($user->hasRole('Beneficiary')) {

        $beneficiary = $user->beneficiary;

        // walang beneficiary record
        if (!$beneficiary) {
            return redirect()->route('onboarding');
        }

        // rejected users balik onboarding
        if ($beneficiary->approval_status === 'rejected') {
            return redirect()->route('onboarding');
        }

        return Inertia::render('Beneficiary/Dashboard', [
            'user' => $user,
            'beneficiary' => $beneficiary,

            // gamitin sa frontend para magpakita pending notice
            'pendingApproval' =>
                $beneficiary->approval_status !== 'approved',
        ]);
    }

    if ($user->hasRole('Employer')) {
        return redirect()->route('employer.dashboard');
    }

    if ($user->hasAnyRole(['Admin', 'PESO Admin'])) {
        return app(DashboardController::class)
            ->index(request());
    }

    if ($user->hasAnyRole(['PESO', 'PESO User'])) {
        return redirect()->route('peso.user.dashboard');
    }

    return redirect()->route('login');

})->name('dashboard');

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

// Fallback to serve certificate files from storage when webserver returns 404
// This helps hosts that don't follow symlinks for `public/storage`.
Route::get('storage/certificates/{path}', function ($path) {
    $full = 'certificates/' . $path;
    if (!Storage::disk('public')->exists($full)) {
        abort(404);
    }
    return Storage::disk('public')->response($full);
})->where('path', '.*');
