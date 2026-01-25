<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\Application;
use App\Models\Attendance;
use App\Models\EmployerRating;
use App\Models\Beneficiary;
use App\Models\Interview;
use App\Services\GoogleCalendarService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployerController extends Controller
{
    // Create Job Listing
    public function storeJob(Request $request)
    {
        $data = $request->validate([
            'title'=>'required|string',
            'description'=>'nullable|string',
            'positions'=>'required|integer',
            'start_date'=>'nullable|date',
            'end_date'=>'nullable|date',
        ]);

        $data['employer_id'] = auth()->user()->id;
        $job = JobListing::create($data);

        return response()->json(['message'=>'Job created successfully','job'=>$job]);
    }

    // View applicants
    public function applicants($jobId)
    {
        return Application::with('beneficiary')
            ->where('job_listing_id',$jobId)
            ->get();
    }

    // Submit Attendance (batch or single)
    public function submitAttendance(Request $request)
    {
        if ($request->has('records')) {
            $data = $request->validate([
                'date'=>'required|date',
                'records'=>'array',
                'records.*.beneficiary_id'=>'required|exists:beneficiaries,id',
                'records.*.time_in'=>'nullable',
                'records.*.time_out'=>'nullable',
                'records.*.notes'=>'nullable|string'
            ]);
            $created = [];
            foreach ($data['records'] as $rec){
                $rec['date']=$data['date'];
                $rec['employer_id']=auth()->id();
                $created[]=Attendance::create($rec);
            }
            return response()->json(['message'=>'Attendance submitted','attendance'=>$created]);
        }

        $data = $request->validate([
            'beneficiary_id'=>'required|exists:beneficiaries,id',
            'date'=>'required|date',
            'time_in'=>'nullable',
            'time_out'=>'nullable',
            'notes'=>'nullable|string'
        ]);
        $data['employer_id']=auth()->id();
        $attendance=Attendance::create($data);

        return response()->json(['message'=>'Attendance submitted','attendance'=>$attendance]);
    }

    // Submit rating
    public function submitRating(Request $request)
    {
        $data = $request->validate([
            'employer_id'=>'required|exists:employers,id',
            'beneficiary_id'=>'required|exists:beneficiaries,id',
            'application_id'=>'nullable|exists:applications,id',
            'punctuality'=>'nullable|integer|min:1|max:5',
            'work_attitude'=>'nullable|integer|min:1|max:5',
            'output_quality'=>'nullable|integer|min:1|max:5',
            'communication'=>'nullable|integer|min:1|max:5',
            'overall'=>'required|integer|min:1|max:5',
            'comment'=>'nullable|string',
        ]);
        $rating = EmployerRating::create($data);
        return response()->json(['message'=>'Rating submitted','rating'=>$rating]);
    }

    // Recommended candidates
    public function recommendedCandidates()
    {
        $recommended = Beneficiary::withAvg('ratings','overall')
            ->withCount(['attendances as attendance_present'=>fn($q)=>$q->whereNotNull('time_in')])
            ->orderByDesc('ratings_avg_overall')
            ->orderByDesc('attendance_present')
            ->limit(10)
            ->get();

        return response()->json($recommended);
    }

    // Applicant ratings
    public function applicantRatings($beneficiaryId)
    {
        return EmployerRating::where('beneficiary_id',$beneficiaryId)->with('employer','application')->get();
    }

    // Choose applicant
    public function chooseApplicant($jobId,$applicationId)
    {
        $job = JobListing::findOrFail($jobId);
        $app = Application::where('job_listing_id',$jobId)->where('id',$applicationId)->firstOrFail();
        $app->status='selected';
        $app->selected_at=now();
        $app->save();

        return response()->json(['message'=>'Applicant selected','application'=>$app]);
    }

    // Interviews
    public function interviews()
    {
        return Interview::where('employer_id',auth()->id())->with(['jobListing','beneficiary'])->get();
    }

    // List attendance
    public function listAttendance()
    {
        return Attendance::where('employer_id',auth()->id())->with('beneficiary')->orderByDesc('date')->limit(200)->get();
    }

    // Submit work outputs
    public function submitWorkOutput(Request $request)
    {
        $request->validate([
            'beneficiary_id'=>'required|integer',
            'files'=>'required|array',
            'files.*'=>'file|max:10240'
        ]);

        $records=[];
        foreach ($request->file('files') as $f){
            $path = $f->store('work_outputs','public');
            $records[]= \App\Models\WorkOutput::create([
                'employer_id'=>auth()->id(),
                'beneficiary_id'=>$request->input('beneficiary_id'),
                'file_path'=>$path,
                'original_name'=>$f->getClientOriginalName()
            ]);
        }

        return response()->json(['message'=>'Work outputs uploaded','records'=>$records]);
    }

    // Employer dashboard
    public function dashboard()
    {
        return Inertia::render('Employer/Dashboard');
    }

    public function stats(Request $request) {
    $days = $request->query('days', 30);
    // Fetch your stats based on $days
    return response()->json([
        'open_jobs' => 5,
        'applicants' => 20,
        'upcoming_interviews' => 2,
        'pending_ratings' => 3,
        'today_attendance' => 10,
        'pipeline' => [
            'applied' => 15,
            'selected' => 5,
            'completed' => 2
        ],
        'applications_over_time' => [
            ['date' => '2026-01-20', 'total' => 2],
            ['date' => '2026-01-21', 'total' => 5],
            ['date' => '2026-01-22', 'total' => 4],
        ]
    ]);
}

public function analyticsApplicantsPerJob() {
    $jobs = Job::all(); // Or whatever logic you use
    $data = $jobs->map(fn($job) => [
        'id' => $job->id,
        'title' => $job->title,
        'total' => $job->applicants_count // make sure you eager load count
    ]);
    return response()->json($data);
}

}
