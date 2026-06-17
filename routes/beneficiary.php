<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Announcement;
use App\Http\Controllers\Beneficiary\BeneficiaryController;
use App\Http\Controllers\Beneficiary\ChangePasswordController;
use App\Http\Controllers\Beneficiary\SettingsController;
use App\Http\Controllers\PESO\ExamController;
use App\Http\Controllers\WorkOutputController;

// =======================
// BENEFICIARY API ROUTES
// =======================



// =======================
// BENEFICIARY API ROUTES
// =======================
Route::middleware([
    'auth',
    'verified',
    'role:Beneficiary'
])
    ->prefix('api/beneficiary')
    ->group(function () {
        Route::get('/ratings/pending', [BeneficiaryController::class, 'pendingRatingsApi']);
        Route::post('/ratings/{id}', [BeneficiaryController::class, 'storeRating'])
            ->middleware('throttle:heavy-actions');
        Route::get('/ratings/history', [BeneficiaryController::class, 'ratingsHistoryApi']);

        Route::get('/history', [BeneficiaryController::class, 'historyApi']);

        Route::get('/application-status', [BeneficiaryController::class, 'applicationStatus']);
        Route::get('/dashboard-stats', [BeneficiaryController::class, 'dashboardStats']);

        Route::get('/notifications', [BeneficiaryController::class, 'notificationsApi']);
        Route::post('/notifications/mark-all-read', [BeneficiaryController::class, 'markAllNotificationsAsRead'])
            ->middleware('throttle:heavy-actions');
        Route::post('/notifications/{id}/mark-read', [BeneficiaryController::class, 'markNotificationAsRead'])
            ->middleware('throttle:heavy-actions');

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

            // All possible document keys used by the system
            $fields = [
                'valid_id',
                'school_enrollment',
                'barangay_certificate',
                'birth_certificate',
                'school_record',
                'osy_certificate',
                'income_proof',
                'displacement_certificate',
                'displacement_proof',
                'termination_notice',
                'parent_valid_id',
            ];

            // Build lookup from both keyed entries and array items with 'type' field
            $docLookup = [];

            // Handle keyed format: $existing['valid_id'] = {...} or string path
            foreach ($fields as $field) {
                if (isset($existing[$field])) {
                    $docLookup[$field] = $existing[$field];
                }
            }

            // Handle array format: [{type: 'valid_id', path: '...', name: '...'}, ...]
            if (is_array($existing)) {
                foreach ($existing as $entry) {
                    if (is_array($entry) && isset($entry['type']) && in_array($entry['type'], $fields, true)) {
                        $docLookup[$entry['type']] = $entry;
                    }
                }
            }

            $documents = [];
            foreach ($fields as $field) {
                $document = $docLookup[$field] ?? null;
                $path = null;
                $status = 'missing';
                $uploadedAt = null;
                $remarks = null;

                if (is_array($document)) {
                    $path = $document['path'] ?? null;
                    $status = $document['status'] ?? ($path ? 'uploaded' : 'missing');
                    $uploadedAt = $document['uploaded_at'] ?? $document['updated_at'] ?? null;
                    $remarks = $document['remarks'] ?? $document['rejection_reason'] ?? $document['reason'] ?? null;
                } elseif ($document) {
                    $path = $document;
                    $status = 'uploaded';
                }

                $documents[] = [
                    'key' => $field,
                    'name' => ucwords(str_replace('_', ' ', $field)),
                    'status' => $status,
                    'path' => $path,
                    'uploaded_at' => $uploadedAt,
                    'remarks' => $remarks,
                ];
            }

            return response()->json($documents);
        });

        Route::get('/interviews', [BeneficiaryController::class, 'interviewsApi']);
        Route::get('/exams', [ExamController::class, 'apiExams']);
        Route::get('/attendance', [BeneficiaryController::class, 'attendance']);
        Route::post('/dtr', [BeneficiaryController::class, 'storeDTR'])
            ->middleware('throttle:heavy-actions');
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
            $jobTitle = $beneficiary->job_title ?? null;
            $jobId = $beneficiary->job_id ?? ($beneficiary->job_listing_id ?? null);
            $jobListing = $jobId ? \App\Models\JobListing::find($jobId) : null;

            if ($jobListing) {
                $employer = $employer ?: $jobListing->employer;
                $jobTitle = $jobListing->title ?? $jobTitle;
                $jobId = $jobListing->id;
            }

            $application = \App\Models\Application::where('beneficiary_id', $beneficiary->id)
                ->with('jobListing.employer')
                ->where(function ($query) use ($beneficiary) {
                    $query->whereIn('status', ['assigned', 'ongoing', 'deployed', 'for_contract', 'contract_signed'])
                        ->orWhereNotNull('job_listing_id')
                        ->orWhereHas('jobListing', function ($q) use ($beneficiary) {
                            $q->where('assigned_beneficiary_id', $beneficiary->id);
                        });
                })
                ->latest()
                ->first();

            if (!$jobListing && $application?->jobListing) {
                $jobListing = $application->jobListing;
                $employer = $employer ?: $jobListing->employer;
                $jobTitle = $jobListing->title ?? $jobTitle;
                $jobId = $jobListing->id;
            }

            if (!$employer) {
                if ($application?->jobListing?->employer) {
                    $employer = $application->jobListing->employer;
                    $jobListing = $application->jobListing;
                    $jobTitle = $jobListing->title ?? $jobTitle;
                    $jobId = $jobListing->id;
                }
            }

            if (!$employer) {
                return response()->json(null);
            }

            // Extract work schedule from employer details JSON or job listing
            $workSchedule = null;
            $details = is_array($employer->details) ? $employer->details : [];
            $scheduleDay = $details['spes_participation']['work_schedules'] ?? null;
            $scheduleTime = $details['employment_opportunities']['work_schedule'] ?? null;
            if ($scheduleDay && $scheduleTime) {
                $workSchedule = trim($scheduleDay) . ', ' . trim($scheduleTime);
            } elseif ($scheduleDay) {
                $workSchedule = trim($scheduleDay);
            } elseif ($scheduleTime) {
                $workSchedule = trim($scheduleTime);
            }

            // Location: prefer job listing location, fall back to employer address
            $location = $jobListing->location ?? $employer->address ?? null;

            // Contact person from employer record
            $contactPerson = $employer->contact_person ?? null;

            return response()->json([
                'company' => $employer->company_name ?? 'Unknown Company',
                'job_title' => $jobTitle ?? 'Assigned Position',
                'job_id' => $jobId,
                'work_schedule' => $workSchedule,
                'location' => $location,
                'contact_person' => $contactPerson,
                'status' => 'Assigned',
            ]);
        });
    });

// =======================
// BENEFICIARY PAGES (Inertia)
// =======================
Route::middleware(['auth', 'verified', 'role:Beneficiary'])
    ->prefix('beneficiary')
    ->name('beneficiary.')
    ->group(function () {


    Route::get('/upload-documents', function () {
    return Inertia::render('Beneficiary/Upload');
})->name('page.uploadDocuments');
     Route::get('/', function () {

    $beneficiary =
        auth()->user()->beneficiary;

    return Inertia::render(
        'Beneficiary/Dashboard',
        [
            'beneficiary' => $beneficiary,

            'pendingApproval' =>
                optional($beneficiary)
                    ->approval_status !== 'approved',
        ]
    );

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
        Route::get('/certificate', [BeneficiaryController::class, 'certificate'])->name('certificate');

        Route::get('/notifications', function () {
            $user = auth()->user();
            $announcements = Announcement::latest()
                ->whereIn('target_role', ['all', 'beneficiary'])
                ->get()
                ->map(function ($announcement) use ($user) {
                    return [
                        'id' => $announcement->id,
                        'title' => $announcement->title,
                        'content' => $announcement->content,
                        'image' => $announcement->image,
                        'created_at' => $announcement->created_at,
                        'read' => $announcement->isReadBy($user),
                    ];
                });

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
        Route::get('/work-outputs', [WorkOutputController::class, 'beneficiaryIndex'])->name('work-outputs.index');
        Route::post('/work-outputs', [WorkOutputController::class, 'beneficiaryStore'])->name('work-outputs.store');
        Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('change-password');
    });
