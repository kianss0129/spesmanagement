<?php


namespace App\Http\Controllers\Employer;


use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Beneficiary;
use Illuminate\Http\Request;


class BeneficiaryController extends Controller
{
    // List beneficiaries assigned to this employer
    public function index()
    {
        $employer = auth()->user()->employer;

        if (! $employer) {
            return response()->json(['beneficiaries' => []]);
        }

        $applications = Application::with(['beneficiary.user', 'jobListing'])
            ->whereHas('jobListing', function ($query) use ($employer) {
                $query->where('employer_id', $employer->id);
            })
            ->whereIn('status', ['assigned', 'deployed', 'ongoing', 'completion_review', 'completed'])
            ->latest()
            ->get();

        return response()->json([
            'beneficiaries' => $applications->map(function ($application) {
                $beneficiary = $application->beneficiary;

                if (! $beneficiary) {
                    return null;
                }

                $status = 'Active';
                if (! $application->employer_acknowledged_at && $application->status === 'assigned') {
                    $status = 'Pending Acknowledgement';
                } elseif ($application->status === 'completion_review') {
                    $status = 'Completion Review';
                } elseif ($application->status === 'completed') {
                    $status = 'Completed';
                } elseif ($application->employer_acknowledged_at) {
                    $status = 'Acknowledged';
                }

                return [
                    'id' => $beneficiary->id,
                    'application_id' => $application->id,
                    'first_name' => $beneficiary->first_name,
                    'last_name' => $beneficiary->last_name,
                    'middle_name' => $beneficiary->middle_name,
                    'suffix' => $beneficiary->suffix,
                    'job_title' => $application->jobListing?->title,
                    'assignment_date' => $application->updated_at,
                    'assignment_status' => $status,
                    'application_status' => $application->status,
                    'employer_acknowledged_at' => $application->employer_acknowledged_at,
                    'profile_photo_url' => $beneficiary->user?->profile_photo_path ? asset('storage/' . $beneficiary->user->profile_photo_path) : '/default-profile.png',
                ];
            })->filter()->values(),
        ]);
    }


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
        $employer = auth()->user()->employer;

        if (! $employer) {
            return response()->json(['message' => 'Employer not found.'], 403);
        }

        $application = Application::with('jobListing')
            ->where('beneficiary_id', $beneficiary->id)
            ->whereHas('jobListing', fn ($query) => $query->where('employer_id', $employer->id))
            ->whereIn('status', ['contract_signed', 'deployed', 'ongoing'])
            ->latest()
            ->first();

        if (! $application) {
            return response()->json([
                'message' => 'No eligible application found for completion review.',
            ], 422);
        }

        $beneficiary->employment_status = 'employed';
        $beneficiary->save();

        $application->update(['status' => 'completion_review']);


        return response()->json([
            'message' => "Beneficiary {$beneficiary->first_name} {$beneficiary->last_name} submitted for CPESO completion review."
        ]);
    }
}
