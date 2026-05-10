<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Announcement;
use App\Http\Controllers\Beneficiary\BeneficiaryController;
use App\Http\Controllers\Beneficiary\ChangePasswordController;
use App\Http\Controllers\Beneficiary\SettingsController;
use App\Http\Controllers\PESO\ExamController;

// =======================
// BENEFICIARY API ROUTES
// =======================
Route::middleware(['auth', 'verified', 'role:Beneficiary', 'beneficiary.approved'])
    ->prefix('api/beneficiary')
    ->group(function () {
        Route::get('/ratings/pending', [BeneficiaryController::class, 'pendingRatingsApi']);
        Route::post('/ratings/{id}', [BeneficiaryController::class, 'storeRating']);
        Route::get('/ratings/history', [BeneficiaryController::class, 'ratingsHistoryApi']);

        Route::get('/history', [BeneficiaryController::class, 'historyApi']);

        Route::get('/application-status', [BeneficiaryController::class, 'applicationStatus']);
        Route::get('/dashboard-stats', [BeneficiaryController::class, 'dashboardStats']);

        Route::get('/notifications', function () {
            $role = 'beneficiary';
            $announcements = Announcement::latest()
                ->whereIn('target_role', ['all', $role])
                ->take(10)
                ->get();

            return response()->json($announcements);
        });

        Route::get('/profile', function () {
            $user = auth()->user();
            $beneficiary = $user->beneficiary;

            return response()->json([
                'user' => [
                    'name' => $user->name,
                ],
                'approval_status' => $beneficiary->approval_status ?? null,
                'employer' => $beneficiary->employer
                    ? [
                        'company' => $beneficiary->employer->company_name,
                        'job_title' => $beneficiary->job_title,
                    ]
                    : null,
                'work_schedule' => $beneficiary->workSchedules ?? [],
                'evaluation' => $beneficiary->evaluations ?? [],
                'work_history' => $beneficiary->workHistory ?? [],
            ]);
        });

        Route::get('/documents', function () {
            $beneficiary = auth()->user()->beneficiary;

            if (!$beneficiary) {
                return response()->json([]);
            }

            $existing = $beneficiary->documents ?? [];
            if (is_string($existing)) {
                $existing = json_decode($existing, true) ?: [];
            }

            $fields = [
                'birth_certificate',
                'school_record',
                'osy_certificate',
                'income_proof',
                'displacement_certificate',
                'termination_notice'
            ];

            $documents = [];
            foreach ($fields as $field) {
                $documents[] = [
                    'name' => ucwords(str_replace('_', ' ', $field)),
                    'status' => isset($existing[$field]) ? 'uploaded' : 'pending',
                    'path' => $existing[$field] ?? null,
                ];
            }

            return response()->json($documents);
        });

        Route::get('/interviews', [BeneficiaryController::class, 'interviewsApi']);
        Route::get('/exams', [ExamController::class, 'apiExams']);
        Route::get('/attendance', [BeneficiaryController::class, 'attendance']);
        Route::post('/dtr', [BeneficiaryController::class, 'storeDTR']);
        Route::get('/recent-activities', [BeneficiaryController::class, 'recentActivities']);
        Route::get('/activities/data', [BeneficiaryController::class, 'allActivities'])
        ->name('activities.data');

        Route::get('/applications', [BeneficiaryController::class, 'listApplications']);
        Route::get('/jobs', [BeneficiaryController::class, 'jobs']);

        Route::get('/assigned-employer', function () {
            $beneficiary = auth()->user()->beneficiary;

            if (!$beneficiary) {
                return response()->json(null);
            }

            $employer = $beneficiary->employer;
            if (!$employer) {
                return response()->json(null);
            }

            return response()->json([
                'company' => $employer->company_name ?? 'Unknown Company',
                'job_title' => $beneficiary->job_title ?? 'Assigned Position',
                'job_id' => $beneficiary->job_listing_id ?? null,
                'status' => 'Assigned',
            ]);
        });
    });

// =======================
// BENEFICIARY PAGES (Inertia)
// =======================
Route::middleware(['auth', 'verified', 'role:Beneficiary', 'beneficiary.approved'])
    ->prefix('beneficiary')
    ->name('beneficiary.')
    ->group(function () {
        Route::get('/', function () {
            return Inertia::render('Beneficiary/Dashboard');
        })->name('dashboard');

        Route::get('/upload', function () {
            return Inertia::render('Beneficiary/Upload');
        })->name('upload');

        Route::get('/profile', [BeneficiaryController::class, 'profilePage'])->name('profile');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

        Route::get('/jobs', function () {
            return Inertia::render('Beneficiary/Jobs');
        })->name('jobs');

        Route::get('/applications', [BeneficiaryController::class, 'listApplications'])->name('applications');
        Route::get('/exams', [ExamController::class, 'beneficiaryExams'])->name('exams');

        Route::get('/notifications', function () {
            $user = auth()->user();
            $announcements = Announcement::latest()
                ->whereIn('target_role', ['all', 'beneficiary'])
                ->get();

            return Inertia::render('Beneficiary/Notifications', [
                'user' => $user,
                'announcements' => $announcements,
            ]);
        })->name('notifications');

        Route::get('/interviews', [BeneficiaryController::class, 'interviewsPage'])->name('interviews');
        Route::get('/attendance', function () {
            return Inertia::render('Beneficiary/Attendance');
        })->name('attendance');

        Route::get('/ratings', [BeneficiaryController::class, 'pendingRatings'])->name('ratings');
        Route::get('/ratings/history', [BeneficiaryController::class, 'ratingsHistory'])->name('ratings.history');
        Route::get('/history', [BeneficiaryController::class, 'history'])->name('history');
        Route::get('/activities', function () {
            return Inertia::render('Beneficiary/Activities');
        })->name('activities');
        Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('change-password');
    });
