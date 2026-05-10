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
use App\Models\Announcement;

// =======================
// PESO / PESO Admin Routes
// =======================
Route::middleware(['web', 'auth', 'role:PESO Admin|PESO|Admin'])
    ->prefix('peso')
    ->name('peso.')
    ->group(function () {
        Route::post('assign-beneficiary', [PESOController::class, 'assignBeneficiary'])->name('assignBeneficiary');
        Route::post('schedule-interview', [InterviewController::class, 'schedule'])->name('scheduleInterview');
        Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
        Route::post('interviews/{id}/result', [InterviewController::class, 'updateResult'])->name('interviews.updateResult');
        Route::post('exams/{id}/result', [ExamController::class, 'updateResult'])->name('exams.updateResult');
        Route::post('contracts', [ContractController::class, 'store'])->name('contracts.store');
        Route::post('contracts/{id}/result', [ContractController::class, 'updateResult'])->name('contracts.updateResult');
        Route::post('users/create-peso', [PESOController::class, 'createPeso'])->name('users.create-peso');

        Route::get('exams/upcoming', [ExamController::class, 'upcomingExams'])->name('exams.upcoming');
        Route::get('interviews/upcoming', [InterviewController::class, 'upcoming'])->name('interviews.upcoming');
        Route::get('contracts/upcoming', [ContractController::class, 'upcomingContracts'])->name('contracts.upcoming');

        Route::post('reports', [PESOController::class, 'storeReport'])->name('reports.store');
        Route::get('reports', [PESOController::class, 'getReports'])->name('reports.index');
        Route::get('reports/dole', [PESOController::class, 'generateDOLEReport']);
        Route::get('job-listings', [PESOController::class, 'jobListings'])->name('jobListings');

        Route::post('announcements', function (Request $request) {
            if (!auth()->user()->hasAnyRole(['Admin', 'PESO Admin'])) {
                abort(403);
            }

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
        Route::post('beneficiaries/{id}/undo', [PESOController::class, 'undoApproveBeneficiary'])->name('beneficiaries.undo');
        Route::post('beneficiaries/{id}/revert', [PESOController::class, 'revertBeneficiary'])->name('beneficiaries.revert');

        Route::get('employers/pending', [PESOController::class, 'pendingEmployers'])->name('employers.pending');
        Route::get('employers/approved', [PESOController::class, 'approvedEmployers'])->name('employers.approved');
        Route::get('employers/compliance-page', [PESOController::class, 'employerCompliancePage'])->name('employers.compliance.page');
        Route::get('employers/{employer}/applications', [PESOController::class, 'viewEmployerApplications'])->name('employers.applications');
        Route::post('employers/{id}/undo', [PESOController::class, 'undoApproveEmployer'])->name('employers.undo');
        Route::post('employers/{id}/revert', [PESOController::class, 'revertEmployer'])->name('employers.revert');

        Route::middleware(['role:PESO Admin'])->group(function () {
            Route::post('beneficiaries/{id}/approve', [PESOController::class, 'approveBeneficiary'])->name('beneficiaries.approve');
            Route::post('beneficiaries/{id}/reject', [PESOController::class, 'rejectBeneficiary'])->name('beneficiaries.reject');
            Route::post('employers/{id}/approve', [PESOController::class, 'approveEmployer'])->name('employers.approve');
            Route::post('employers/{id}/reject', [PESOController::class, 'rejectEmployer'])->name('employers.reject');
            Route::get('beneficiaries/{beneficiary}/verify', [BeneficiaryController::class, 'showVerification'])->name('beneficiaries.verify.show');
            Route::post('beneficiaries/{beneficiary}/verify', [BeneficiaryController::class, 'verify'])->name('beneficiaries.verify');
        });

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
        Route::get('analytics/employer-reliability', [PESOController::class, 'getEmployerReliability']);
        Route::get('interview/history', [PESOController::class, 'getInterviewHistory']);
        Route::get('beneficiaries/{id}/work-history', [BeneficiaryController::class, 'workHistory'])->name('beneficiary.workHistory');
    });

Route::middleware('auth')->get('peso/reports/dole', [PESOController::class, 'exportDOLEReport'])->name('peso.reports.dole');
