<?php




use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\Admin\AdminAssignmentController;
use App\Http\Controllers\RoleController;




// =======================
// ADMIN ROUTES
// =======================
Route::middleware(['auth', 'role:Admin|PESO Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {




        // =====================
        // DASHBOARD & SETTINGS
        // =====================
        Route::get('settings', [AdminController::class, 'settings'])->name('settings');
        Route::get('stats', [AdminController::class, 'stats'])->name('stats');




        // =====================
        // ACTIVITY LOG
        // =====================
        Route::get('activity', [AdminController::class, 'activity'])->name('activity');




        // =====================
        // USER EXPORTS
        // =====================
        Route::get('export-users', [AdminController::class, 'exportUsers'])->name('export.users');




        // =====================
        // ROLE MANAGEMENT
        // =====================
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/json', [RoleController::class, 'getJson'])->name('roles.json');
        Route::post('roles/assign', [RoleController::class, 'assign'])->name('roles.assign')
            ->middleware('throttle:heavy-actions');
        Route::delete('roles/{user}', [RoleController::class, 'remove'])->name('roles.remove')
            ->middleware('throttle:heavy-actions');




        // =====================
        // USER MANAGEMENT
        // =====================
        Route::delete('users/{user}', [RoleController::class, 'destroy'])->name('users.destroy');
        Route::get('users/approve/{id}', [UserApprovalController::class, 'approve'])->name('users.approve');




        // =====================
        // BENEFICIARY ASSIGNMENT MODULE
        // =====================
        Route::get('assignment', function () {
            return inertia('Admin/BeneficiaryAssignment');
        })->name('assignment.index');


        Route::get('beneficiaries', [AdminAssignmentController::class, 'getBeneficiaries'])->name('beneficiaries.list');
        Route::get('beneficiaries/{id}', [AdminAssignmentController::class, 'getBeneficiaryProfile'])->name('beneficiaries.profile');
        Route::get('jobs', [AdminAssignmentController::class, 'getAllJobs'])->name('jobs.list');
        Route::get('jobs/matched', [AdminAssignmentController::class, 'getAdminMatchedJobs'])->name('jobs.matched');
        Route::get('jobs/{jobId}/matching-beneficiaries', [AdminAssignmentController::class, 'getMatchingSuggestions'])->name('jobs.matching');
        Route::get('jobs/{jobId}/debug-matching', [AdminAssignmentController::class, 'debugMatchingSuggestions'])->name('jobs.debug-matching');
        Route::get('employers', [AdminAssignmentController::class, 'getAllEmployers'])->name('employers.list');
        Route::post('beneficiaries/{beneficiaryId}/assign', [AdminAssignmentController::class, 'assignBeneficiary'])->name('beneficiaries.assign')
            ->middleware('throttle:heavy-actions');
        Route::get('skills-for-filter', [AdminAssignmentController::class, 'getSkillsForFilter'])->name('skills.filter');
        Route::get('education-levels', [AdminAssignmentController::class, 'getEducationLevels'])->name('education.levels');
    });


// =======================
// PUBLIC ONBOARDING ROUTES (for beneficiaries)
// =======================
Route::middleware('auth')->group(function () {
    Route::get('onboarding/jobs', [AdminAssignmentController::class, 'getAllJobs'])->name('onboarding.jobs');
    Route::get('onboarding/skills', [AdminAssignmentController::class, 'getSkillsForFilter'])->name('onboarding.skills');
});







