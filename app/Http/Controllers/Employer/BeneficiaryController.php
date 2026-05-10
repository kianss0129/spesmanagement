<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use Illuminate\Http\Request;

class BeneficiaryController extends Controller
{
    // Work Schedules page
    public function workSchedules()
    {
        // TODO: implement logic to show work schedules
        return response()->json(['message' => 'Work schedules endpoint']);
    }

    // Save schedule for a beneficiary
    public function saveSchedule($id, Request $request)
    {
        $beneficiary = Beneficiary::findOrFail($id);

        // TODO: implement logic to save schedule
        return response()->json([
            'message' => "Schedule saved for {$beneficiary->first_name} {$beneficiary->last_name}"
        ]);
    }

    // Show work history
    public function history($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);

        // Assuming you have a workHistory() relationship
        $timeline = $beneficiary->workHistory()->get();

        return response()->json([
            'beneficiary' => $beneficiary->first_name . ' ' . $beneficiary->last_name,
            'timeline' => $timeline
        ]);
    }

    // Mark beneficiary as completed
    public function complete($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);
        $beneficiary->is_completed = true;
        $beneficiary->save();

        return response()->json([
            'message' => "Beneficiary {$beneficiary->first_name} {$beneficiary->last_name} marked as completed."
        ]);
    }
}