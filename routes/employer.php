<?php


use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Employer\EmployerController;
use App\Http\Controllers\Employer\JobController;
use App\Http\Controllers\Employer\BeneficiaryController as EmployerBeneficiaryController;
use App\Http\Controllers\Employer\BeneficiaryRatingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Employer\EmployerOnboardingController;
use App\Http\Controllers\WorkOutputController;



// =======================
// ONBOARDING PENDING PAGE
// =======================
Route::middleware(['auth'])->group(function () {


    Route::get('/onboarding/pending', function () {


        $employer = auth()->user()->employer;


        return Inertia::render('Onboarding/Pending', [
            'status' => $employer->approval_status ?? 'pending',
        ]);


    })->name('onboarding.pending');


});


// =======================
// EMPLOYER ROUTES
// =======================
Route::middleware(['auth', 'verified', 'role:Employer'])
    ->prefix('employer')
    ->name('employer.')
    ->group(function () {
        Route::get('/', [EmployerController::class, 'dashboard'])->name('dashboard');


        // Notifications
        Route::get('notifications', [EmployerController::class, 'notificationsPage'])->name('notifications');
        Route::get('notifications/data', [EmployerController::class, 'notificationsData'])->name('notifications.data');
        Route::post('notifications/mark-all-read', [EmployerController::class, 'markAllNotificationsAsRead'])->name('notifications.markAllRead');
        Route::post('notifications/{id}/mark-read', [EmployerController::class, 'markNotificationAsRead'])->name('notifications.markRead');


        // Settings
        Route::get('settings', [EmployerController::class, 'settings'])->name('settings');


        Route::middleware(['employer.approved'])->group(function () {
            Route::get('reports/history', [EmployerController::class, 'reportHistory']);


        // Jobs
        Route::get('jobs/matched', [JobController::class, 'matchedJobs'])->name('jobs.matched');
        Route::resource('jobs', JobController::class);
        Route::post('jobs/{id}/interview', [EmployerController::class, 'scheduleInterview'])->name('jobs.scheduleInterview');
        Route::post('jobs/{id}/rate/{beneficiary}', [EmployerController::class, 'submitRating'])->name('jobs.submitRating');
        Route::get('jobs/{id}/applicants', [EmployerController::class, 'applicants'])->name('jobs.applicants');
        Route::get('jobs/{job}/edit', [JobController::class, 'edit'])->name('jobs.edit');


        // Interviews
        Route::post('interviews/schedule', [EmployerController::class, 'scheduleInterview'])->name('interviews.schedule');
        Route::get('interviews/data', [EmployerController::class, 'interviews']);
        Route::get('interviews', [PageController::class, 'employerInterviews'])->name('interviews');


        // Ratings
        Route::get('ratings', function () {
            return Inertia::render('Employer/Ratings');
        })->name('ratings');
        Route::post('ratings', [BeneficiaryRatingController::class, 'store'])->name('ratings.store');
        Route::get('ratings/data', [BeneficiaryRatingController::class, 'index']);
        Route::get('ratings/pending', function () {
            return Inertia::render('Employer/PendingRatings');
        })->name('ratings.pending');
        Route::get('ratings/history', function () {
            return Inertia::render('Employer/RatingsHistory');
        })->name('ratings.history');
        Route::get('ratings/history/data', [EmployerController::class, 'ratingsHistoryData'])->name('ratings.history.data');
        Route::get('recent-activities', [EmployerController::class, 'recentActivities'])
        ->name('recentActivities');
        Route::get('activities', function () {
            return Inertia::render('Employer/Activities');
        })->name('activities');
        Route::get('activities/data', [EmployerController::class, 'allActivities'])
        ->name('activities.data');




        // Applicants and beneficiary management
        Route::get('beneficiaries', [EmployerBeneficiaryController::class, 'index']);
        Route::get('applicants', [EmployerController::class, 'index'])->name('applicants');
        Route::get('completion-rate', [EmployerController::class, 'completionRate'])->name('completion-rate');
        Route::post('applications/{application}/acknowledge', [EmployerController::class, 'acknowledgeAssignment'])->name('applications.acknowledge');
        Route::get('applications', [EmployerController::class, 'applicationsForDropdown'])->name('applications.dropdown');
        Route::get('beneficiary-history', function () {
            return Inertia::render('Employer/BeneficiaryHistory');
        })->name('beneficiary.history');
        Route::get('schedules', [EmployerBeneficiaryController::class, 'workSchedules']);
        Route::post('beneficiaries/{id}/schedule', [EmployerBeneficiaryController::class, 'saveSchedule']);
        Route::get('beneficiary/{id}/history', [EmployerBeneficiaryController::class, 'history']);
        Route::post('beneficiary/{id}/complete', [EmployerBeneficiaryController::class, 'complete']);

        // Contract signing schedules for this employer's beneficiaries
        Route::get('contracts/scheduled', function () {
            $user = auth()->user();
            $employer = $user->employer;

            if (!$employer) {
                return response()->json([]);
            }

            $beneficiaryIds = \App\Models\Beneficiary::where('employer_id', $employer->id)->pluck('id');
            $applicationIds = \App\Models\Application::whereIn('beneficiary_id', $beneficiaryIds)->pluck('id');

            $contracts = \App\Models\Contract::with(['application.beneficiary'])
                ->whereIn('application_id', $applicationIds)
                ->whereIn('status', ['scheduled', 'rescheduled'])
                ->orderBy('contract_date')
                ->get()
                ->map(function ($contract) {
                    $beneficiary = $contract->application?->beneficiary;
                    $name = $beneficiary
                        ? trim(($beneficiary->first_name ?? '') . ' ' . ($beneficiary->last_name ?? ''))
                        : 'Unknown';

                    return [
                        'id' => $contract->id,
                        'beneficiary_name' => $name ?: 'Unknown',
                        'contract_date' => $contract->contract_date?->toISOString(),
                        'end_at' => $contract->end_at?->toISOString(),
                        'location' => $contract->location,
                        'batch_title' => $contract->batch_title,
                        'status' => $contract->status,
                        'result' => $contract->result,
                    ];
                });

            return response()->json($contracts);
        });


        /* ✅ ADD THIS HERE */
        Route::post('job-status/{id}', [EmployerController::class, 'updateJobStatus'])->name('job-status.update');
        Route::post('job-status/{id}/certificate', [EmployerController::class, 'uploadCertificate'])->name('job-status.certificate');


        // Analytics & reporting
        Route::get('stats', [EmployerController::class, 'stats'])->name('stats');
        Route::get('analytics/applicants-per-job', [EmployerController::class, 'analyticsApplicantsPerJob'])->name('analytics.applicantsPerJob');
        Route::get('recommended', [PageController::class, 'employerRecommended'])->name('recommended');
        Route::get('interviews', [PageController::class, 'employerInterviews'])->name('interviews');
        Route::get('performance', [PageController::class, 'employerPerformance'])->name('performance');
        Route::get('work-output', [WorkOutputController::class, 'employerIndex'])->name('workOutput');
        Route::get('work-outputs', [WorkOutputController::class, 'employerIndex'])->name('workOutputs.index');
        Route::patch('work-outputs/{workOutput}/approve', [WorkOutputController::class, 'approve'])->name('workOutputs.approve');
        Route::patch('work-outputs/{workOutput}/reject', [WorkOutputController::class, 'reject'])->name('workOutputs.reject');
        Route::get('reports', [PageController::class, 'employerReports'])->name('reports');
        Route::post('reports', [EmployerController::class, 'storeReport'])->name('reports.store');
        Route::get('attendance', [PageController::class, 'employerAttendance'])->name('attendance');
        Route::get('attendance/data', [EmployerController::class, 'attendance'])
            ->name('attendance.data');
        Route::patch('attendance/{attendance}/review', [EmployerController::class, 'reviewAttendance'])
            ->name('attendance.review');
            
        });

    });
