<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PESO\PESOController;
use App\Http\Controllers\PESO\AnalyticsController;
use App\Http\Controllers\PESO\InterviewController;
use App\Http\Controllers\PESO\ExamController;
use App\Http\Controllers\PESO\ContractController;
use App\Http\Controllers\Beneficiary\BeneficiaryController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminAssignmentController;
use App\Models\Announcement;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkOutputController;

// =======================
// PESO / PESO Admin Routes
// =======================
Route::middleware(['web', 'auth', 'role:Super Admin|Admin|PESO Admin'])
    ->prefix('peso')
    ->name('peso.')
    ->group(function () {
        Route::post('assign-beneficiary', [PESOController::class, 'assignBeneficiary'])
            ->name('assignBeneficiary')
            ->middleware('throttle:heavy-actions');
        Route::post('schedule-interview', [InterviewController::class, 'schedule'])
            ->name('scheduleInterview')
            ->middleware('throttle:heavy-actions');
        Route::post('exams', [ExamController::class, 'store'])
            ->name('exams.store')
            ->middleware('throttle:heavy-actions');
        Route::post('interviews/{id}/result', [InterviewController::class, 'updateResult'])
            ->name('interviews.updateResult')
            ->middleware('throttle:heavy-actions');
        Route::post('exams/{id}/result', [ExamController::class, 'updateResult'])
            ->name('exams.updateResult')
            ->middleware('throttle:heavy-actions');
        Route::post('contracts', [ContractController::class, 'store'])
            ->name('contracts.store')
            ->middleware('throttle:heavy-actions');
        Route::post('contracts/{id}/result', [ContractController::class, 'updateResult'])
            ->name('contracts.updateResult')
            ->middleware('throttle:heavy-actions');
        Route::patch('interviews/{interview}/reschedule', [InterviewController::class, 'reschedule'])
            ->name('interviews.reschedule')
            ->middleware('throttle:heavy-actions');
        Route::patch('exams/{exam}/reschedule', [ExamController::class, 'reschedule'])
            ->name('exams.reschedule')
            ->middleware('throttle:heavy-actions');
        Route::patch('contracts/{contract}/reschedule', [ContractController::class, 'reschedule'])
            ->name('contracts.reschedule')
            ->middleware('throttle:heavy-actions');
        Route::get('users/interviewers', [PESOController::class, 'interviewers'])->name('users.interviewers');
        Route::post('users/create-peso', [PESOController::class, 'createPeso'])
            ->name('users.create-peso')
            ->middleware('throttle:heavy-actions');

        Route::get('exams/upcoming', [ExamController::class, 'upcomingExams'])->name('exams.upcoming');
        Route::get('interviews/upcoming', [InterviewController::class, 'upcoming'])->name('interviews.upcoming');
        Route::get('contracts/upcoming', [ContractController::class, 'upcomingContracts'])->name('contracts.upcoming');

        Route::post('reports', [PESOController::class, 'storeReport'])->name('reports.store')
            ->middleware('throttle:heavy-actions');
        Route::get('reports', [PESOController::class, 'getReports'])->name('reports.index');
        Route::get('reports/dole', [PESOController::class, 'exportDOLEReport'])->name('reports.dole');
        Route::get('audit-trail', [PESOController::class, 'auditTrail'])->name('auditTrail');
        Route::get('job-listings', [PESOController::class, 'jobListings'])->name('jobListings');
        Route::get('notifications', [PESOController::class, 'notifications'])->name('notifications');
        Route::get('jobs/{jobId}/matching-beneficiaries', [AdminAssignmentController::class, 'getMatchingSuggestions']);


        // Only Super Admin, Admin and PESO Admin can create or manage announcements
        Route::middleware(['role:Super Admin|Admin|PESO Admin'])->group(function () {
            Route::post('announcements', function (Request $request) {
                $request->validate([
                    'title' => 'required|string|max:255',
                    'message' => 'required|string',
                    'target_role' => 'required|in:all,beneficiary,employer',
                    'image' => 'nullable|image|max:2048',
                ]);

                $imagePath = null;
                if ($request->hasFile('image')) {
                    $imagePath = $request->file('image')->store('announcements', 'public');
                }

                Announcement::create([
                    'title' => $request->title,
                    'content' => $request->message,
                    'target_role' => $request->target_role,
                    'image' => $imagePath,
                ]);

                activity()
                    ->causedBy(auth()->user())
                    ->log('Posted a new announcement: ' . $request->title);

                if ($request->header('X-Inertia') || $request->wantsJson()) {
                    session()->flash('success', 'Announcement posted successfully.');
                    return response()->json(['success' => true]);
                }

                return back()->with('success', 'Announcement posted successfully.');
            })->middleware('throttle:heavy-actions');

            Route::patch('announcements/{announcement}', function (Request $request, Announcement $announcement) {
                $request->validate([
                    'title' => 'required|string|max:255',
                    'message' => 'required|string',
                    'target_role' => 'required|in:all,beneficiary,employer',
                    'image' => 'nullable|image|max:2048',
                ]);

                $data = [
                    'title' => $request->title,
                    'content' => $request->message,
                    'target_role' => $request->target_role,
                ];

                if ($request->hasFile('image')) {
                    $data['image'] = $request->file('image')->store('announcements', 'public');
                }

                $announcement->update($data);

                activity()
                    ->causedBy(auth()->user())
                    ->log('Updated announcement: ' . $request->title);

                return response()->json([
                    'success' => true,
                    'announcement' => $announcement->fresh(),
                ]);
            })->middleware('throttle:heavy-actions');

            Route::delete('announcements/{announcement}', function (Announcement $announcement) {
                $title = $announcement->title;
                $announcement->delete();

                activity()
                    ->causedBy(auth()->user())
                    ->log('Deleted announcement: ' . $title);

                return response()->json(['success' => true]);
            })->middleware('throttle:heavy-actions');
        });

        Route::get('announcements', function () {
            return response()->json(Announcement::latest()->get());
        });

        Route::get('beneficiaries/pending', [PESOController::class, 'pendingBeneficiaries'])->name('beneficiaries.pending');
        Route::get('beneficiaries/approved', [PESOController::class, 'approvedBeneficiaries'])->name('beneficiaries.approved');
        Route::get('beneficiaries/monitoring', [PESOController::class, 'monitoring'])->name('beneficiaries.monitoring');
        Route::get('beneficiaries/{id}/documents', [PESOController::class, 'documents'])->name('beneficiaries.documents');
        Route::get('beneficiaries/{id}/profile', [PESOController::class, 'profile'])->name('beneficiaries.profile');
        Route::get('beneficiaries/{beneficiary}/applications', [PESOController::class, 'viewBeneficiaryApplications'])->name('beneficiaries.applications');
        Route::middleware(['role:Super Admin|Admin|PESO Admin'])->group(function () {
            Route::post('beneficiaries/{id}/undo', [PESOController::class, 'undoApproveBeneficiary'])->name('beneficiaries.undo')
                ->middleware('throttle:heavy-actions');
            Route::post('beneficiaries/{id}/revert', [PESOController::class, 'revertBeneficiary'])->name('beneficiaries.revert')
                ->middleware('throttle:heavy-actions');
        });

        Route::get('employers/pending', [PESOController::class, 'pendingEmployers'])->name('employers.pending');
        Route::get('employers/approved', [PESOController::class, 'approvedEmployers'])->name('employers.approved');
        Route::get('employers/compliance-page', [PESOController::class, 'employerCompliancePage'])->name('employers.compliance.page');
        Route::get('employers/{employer}/applications', [PESOController::class, 'viewEmployerApplications'])->name('employers.applications');
        Route::middleware(['role:Super Admin|Admin|PESO Admin'])->group(function () {
            Route::post('employers/{id}/undo', [PESOController::class, 'undoApproveEmployer'])->name('employers.undo')
                ->middleware('throttle:heavy-actions');
            Route::post('employers/{id}/revert', [PESOController::class, 'revertEmployer'])->name('employers.revert')
                ->middleware('throttle:heavy-actions');
        });

        Route::middleware(['role:Super Admin|Admin|PESO Admin'])->group(function () {
            Route::post('beneficiaries/{id}/approve', [PESOController::class, 'approveBeneficiary'])->name('beneficiaries.approve')
                ->middleware('throttle:heavy-actions');
            Route::post('beneficiaries/{id}/request-correction', [PESOController::class, 'requestBeneficiaryCorrection'])->name('beneficiaries.requestCorrection')
                ->middleware('throttle:heavy-actions');
            Route::post('beneficiaries/{id}/reject', [PESOController::class, 'rejectBeneficiary'])->name('beneficiaries.reject')
                ->middleware('throttle:heavy-actions');
            Route::post('applications/{application}/qualify', [PESOController::class, 'markApplicationQualified'])->name('applications.qualify')
                ->middleware('throttle:heavy-actions');
            Route::post('applications/{application}/deploy', [PESOController::class, 'deployApplication'])->name('applications.deploy')
                ->middleware('throttle:heavy-actions');
            Route::post('applications/{application}/approve-completion', [PESOController::class, 'approveCompletion'])->name('applications.approveCompletion')
                ->middleware('throttle:heavy-actions');
            Route::post('employers/{id}/approve', [PESOController::class, 'approveEmployer'])->name('employers.approve')
                ->middleware('throttle:heavy-actions');
            Route::post('employers/{id}/reject', [PESOController::class, 'rejectEmployer'])->name('employers.reject')
                ->middleware('throttle:heavy-actions');
            Route::get('beneficiaries/{beneficiary}/verify', [BeneficiaryController::class, 'showVerification'])->name('beneficiaries.verify.show');
            Route::post('beneficiaries/{beneficiary}/verify', [BeneficiaryController::class, 'verify'])->name('beneficiaries.verify')
                ->middleware('throttle:heavy-actions');
        });

        Route::get('applications/for-interview', [PESOController::class, 'applicationsForInterview'])->name('applications.forInterview');
        Route::get('applications', [PESOController::class, 'index'])->name('applications.index');

        Route::prefix('analytics')->group(function () {
            Route::get('dashboard', [AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
            Route::get('applicants-by-school', [AnalyticsController::class, 'applicantsBySchool'])->name('analytics.applicantsBySchool');
            Route::get('top-employers', [AnalyticsController::class, 'topEmployers'])->name('analytics.topEmployers');
            Route::get('performance-trends', [AnalyticsController::class, 'performanceTrends'])->name('analytics.performanceTrends');
            Route::get('completion-rate', [AnalyticsController::class, 'completionRate'])->name('analytics.completionRate');
            Route::get('attendance-compliance', [AnalyticsController::class, 'attendanceCompliance'])->name('analytics.attendanceCompliance');
            Route::get('average-ratings', [AnalyticsController::class, 'averageBeneficiaryRatings'])->name('analytics.averageRatings');

            Route::get('employers/compliance', [PESOController::class, 'getEmployerCompliance']);
            Route::get('beneficiaries/ratings', [PESOController::class, 'getBeneficiaryRatings']);
            Route::get('top-beneficiaries', [PESOController::class, 'getTopBeneficiaries']);
            Route::get('applicant-trends', [PESOController::class, 'getApplicantTrends']);
            Route::get('completion-rate', [PESOController::class, 'getCompletionRate']);
            Route::get('job-listings/{jobId}/applications', [PESOController::class, 'viewJobApplications'])->name('analytics.jobApplications');
        });

        Route::get('attendance', [PESOController::class, 'getAttendance']);
        Route::get('work-outputs', [WorkOutputController::class, 'pesoIndex'])->name('workOutputs.index');
        Route::get('analytics/employer-reliability', [PESOController::class, 'getEmployerReliability']);
        Route::get('interview/history', [PESOController::class, 'getInterviewHistory']);
        Route::get('beneficiaries/{id}/work-history', [BeneficiaryController::class, 'workHistory'])->name('beneficiary.workHistory');
    });

Route::middleware(['web', 'auth', 'role:PESO|PESO User'])
    ->prefix('peso-user')
    ->name('peso.user.')
    ->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('interviews', [InterviewController::class, 'assigned'])->name('interviews.assigned');
        Route::post('interviews/{id}/evaluation', [InterviewController::class, 'updateResult'])->name('interviews.evaluate')
            ->middleware('throttle:heavy-actions');
    });

