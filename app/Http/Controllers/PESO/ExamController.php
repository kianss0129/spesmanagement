<?php


namespace App\Http\Controllers\Peso;


use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class ExamController extends Controller
{
    // 🔹 Update exam result (PASS / FAIL)
    public function updateResult(Request $request, $id)
    {
        $validated = $request->validate([
            'result' => 'required|in:passed,failed'
        ]);


        $exam = Exam::with('application')->findOrFail($id);


        // 🚨 جلوگیری re-processing
        if ($exam->status !== 'scheduled') {
            return response()->json([
                'message' => 'Exam already processed.'
            ], 400);
        }


        DB::transaction(function () use ($exam, $validated) {


            $exam->update([
                'status' => 'completed',
                'result' => $validated['result']
            ]);


            if ($validated['result'] === 'passed') {
                $exam->application->update([
                    'status' => 'for_approval'
                ]);
            } else {
                $exam->application->update([
                    'status' => 'rejected'
                ]);
            }
        });


        return response()->json([
            'message' => 'Exam result updated successfully'
        ]);
    }


    // 🔹 Schedule exam
    public function store(Request $request)
    {
        $validated = $request->validate([
            'application_id' => 'required',
            'exam_date' => 'required|date|after_or_equal:now',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $applicationId = $validated['application_id'];

        // Handle unassigned beneficiaries (pseudo application IDs)
        if (str_starts_with($applicationId, 'unassigned_')) {
            $beneficiaryId = str_replace('unassigned_', '', $applicationId);

            // Check if beneficiary exists and is approved
            $beneficiary = \App\Models\Beneficiary::findOrFail($beneficiaryId);
            if (!$beneficiary->approved) {
                return response()->json([
                    'message' => 'Beneficiary is not approved.'
                ], 400);
            }

            // Create a placeholder application for the exam
            $application = \App\Models\Application::create([
                'beneficiary_id' => $beneficiaryId,
                'job_listing_id' => null, // No job assigned yet
                'status' => 'for_exam'
            ]);

            $applicationId = $application->id;
        }

        // 🚨 Prevent duplicate scheduled exam
        $exists = Exam::where('application_id', $applicationId)
            ->where('status', 'scheduled')
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Application already has a scheduled exam.'
            ], 400);
        }

        $exam = Exam::create([
            'application_id' => $applicationId,
            'exam_date' => Carbon::parse($validated['exam_date']),
            'location' => $validated['location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'scheduled'
        ]);

        // 🔥 Update application status
        $exam->application->update([
            'status' => 'for_exam'
        ]);


        return response()->json([
            'message' => 'Exam scheduled successfully'
        ]);
    }


    // 🔹 Beneficiary page (Inertia)
    public function beneficiaryExams()
    {
        $user = auth()->user();
        $beneficiaryId = optional($user->beneficiary)->id;


        if (!$beneficiaryId) {
            return redirect()->back();
        }


        $exams = Exam::whereHas('application', function ($q) use ($beneficiaryId) {
                $q->where('beneficiary_id', $beneficiaryId);
            })
            ->where('exam_date', '>=', Carbon::now())
            ->orderBy('exam_date', 'asc')
            ->take(5)
            ->get()
            ->map(function ($exam) {
                return [
                    'id' => $exam->id,
                    'exam_date' => $exam->exam_date,
                    'location' => $exam->location ?? 'TBA',
                    'status' => $exam->status,
                    'result' => $exam->result,
                ];
            });


        return inertia('Beneficiary/Exams', [
            'exams' => $exams
        ]);
    }


    // 🔹 Admin / dashboard upcoming exams
    public function upcomingExams()
    {
        $exams = Exam::with('application.beneficiary')
            ->where('exam_date', '>=', Carbon::now())
            ->orderBy('exam_date', 'asc')
            ->take(5)
            ->get()
            ->map(function ($exam) {
                return [
                    'id' => $exam->id,
                    'application_id' => $exam->application_id,
                    'beneficiary_name' => $exam->application ? trim(
                        ($exam->application->beneficiary->first_name ?? '') . ' ' . 
                        ($exam->application->beneficiary->last_name ?? '')
                    ) ?: 'N/A' : 'N/A',
                    'exam_date' => $exam->exam_date,
                    'location' => $exam->location ?? 'TBA',
                    'status' => $exam->status ?? 'scheduled',
                    'result' => $exam->result,
                ];
            });


        return response()->json($exams);
    }


    // 🔹 API for beneficiary (AJAX)
    public function apiExams()
    {
        $user = auth()->user();
        $beneficiaryId = optional($user->beneficiary)->id;


        if (!$beneficiaryId) {
            return response()->json([], 403);
        }


        $exams = Exam::whereHas('application', function ($q) use ($beneficiaryId) {
                $q->where('beneficiary_id', $beneficiaryId);
            })
            ->where('exam_date', '>=', Carbon::now())
            ->orderBy('exam_date', 'asc')
            ->take(5)
            ->get()
            ->map(function ($exam) {
                return [
                    'id' => $exam->id,
                    'exam_date' => $exam->exam_date,
                    'location' => $exam->location ?? 'TBA',
                    'status' => $exam->status,
                    'result' => $exam->result,
                ];
            });


        return response()->json($exams);
    }
}

