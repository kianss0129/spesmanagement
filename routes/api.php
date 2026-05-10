<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Peso\ExamController;
use App\Http\Controllers\Beneficiary\BeneficiaryController;
use App\Http\Controllers\Api\InterviewController;

/*
|--------------------------------------------------------------------------
| AUTHENTICATED API (SANCTUM)
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth:sanctum'])->prefix('beneficiary')->group(function () {

    // Profile with all relevant data
    Route::get('/profile', [BeneficiaryController::class, 'profile']);

    // Dashboard stats (optional, can merge with profile)
    Route::get('/dashboard-stats', [BeneficiaryController::class, 'dashboardStats']);

    // Documents
    Route::get('/documents', [BeneficiaryController::class, 'documents']);

    // Application status
    Route::get('/application-status', [BeneficiaryController::class, 'applicationStatus']);

    // Interviews
    Route::get('/interviews', [BeneficiaryController::class, 'interviewsApi']);

    // Interview history
    Route::get('/interviews/history', [BeneficiaryController::class, 'interviewHistoryApi']);

    // Attendance
    Route::get('/attendance', [BeneficiaryController::class, 'attendance']);

    // Notifications
    Route::get('/notifications', [BeneficiaryController::class, 'notifications']);

    // Scheduled contracts
    Route::get('/contracts', [BeneficiaryController::class, 'contractsApi']);

    // Contract history
    Route::get('/contracts/history', [BeneficiaryController::class, 'contractHistoryApi']);

    // Assigned employer (if needed separately)

    Route::get('/assigned-employer', [BeneficiaryController::class, 'assignedEmployer']);
});

// My Interviews (separate controller)
Route::middleware(['web', 'auth:sanctum'])->get('/my/interviews', [InterviewController::class, 'myInterviews'])
    ->name('api.my.interviews');

// Ratings by ID
Route::middleware(['web', 'auth:sanctum'])->prefix('beneficiaries')->group(function () {
    Route::get('{id}/ratings', [BeneficiaryController::class, 'getRatings']);
});
Route::get('/beneficiary/exams', [ExamController::class, 'apiExams']);

// TEST route (optional)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

    Route::get('/beneficiary/application-status', [BeneficiaryController::class, 'applicationStatus']);
});