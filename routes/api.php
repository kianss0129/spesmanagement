<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Beneficiary\BeneficiaryController;
use Illuminate\Http\Request;
use App\Models\Interview;

use App\Http\Controllers\Api\InterviewController;

Route::middleware('auth:sanctum')->get('/my/interviews', [InterviewController::class, 'myInterviews'])->name('api.my.interviews');

Route::middleware('auth')->prefix('beneficiaries')->group(function() {
    Route::get('{id}/ratings', [BeneficiaryController::class,'getRatings']);
});
