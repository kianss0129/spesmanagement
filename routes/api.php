<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Beneficiary\BeneficiaryController;
use Illuminate\Http\Request;
use App\Models\Interview;

Route::middleware('auth:sanctum')->get('/my/interviews', function (Request $request) {
    $uid = $request->user()->id;

    // return interviews where user is beneficiary or employer
    $interviews = Interview::with('jobListing')
        ->where(function($q) use ($uid) {
            $q->where('beneficiary_id', $uid)
              ->orWhere('employer_id', $uid);
        })
        ->orderBy('scheduled_at', 'desc')
        ->get();

    return response()->json($interviews);
});

Route::middleware('auth')->prefix('beneficiaries')->group(function() {
    Route::get('{id}/ratings', [BeneficiaryController::class,'getRatings']);
});
