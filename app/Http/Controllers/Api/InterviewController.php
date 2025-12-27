<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Interview;

class InterviewController extends Controller
{
    public function myInterviews(Request $request)
    {
        $uid = $request->user()->id;

        $interviews = Interview::with('jobListing')
            ->where(function($q) use ($uid) {
                $q->where('beneficiary_id', $uid)
                  ->orWhere('employer_id', $uid);
            })
            ->orderBy('scheduled_at', 'desc')
            ->get();

        return response()->json($interviews);
    }
}
