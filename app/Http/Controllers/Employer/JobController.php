<?php


namespace App\Http\Controllers\Employer;


use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Inertia\Inertia;


class JobController extends Controller
{
   
    // List jobs of logged-in employer
    public function index()
{
    $employer = auth()->user()->employer;


    $jobs = $employer->jobListings()->latest()->get();


    return Inertia::render('Employer/JobList', [
        'jobs' => $jobs
    ]);
}


    // Show create form
    public function create()
    {
        return Inertia::render('Employer/PostJob');
    }


    // Store new job
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'location' => 'required|string',
            'type' => 'required|string',
            'slots' => 'required|integer|min:1',
           'closing_date' => ['required', 'date', 'after_or_equal:today'],
        ]);


       $employer = auth()->user()->employer;


$employer->jobListings()->create($validated);
        return redirect()->route('employer.jobs.index')->with('success', 'Job posted successfully!');
    }


    // Edit job
    public function edit($id)
{
    $employer = auth()->user()->employer;


    $job = $employer->jobListings()->findOrFail($id);


    return Inertia::render('Employer/EditJob', [
        'job' => $job
    ]);
}


    // Update job
    public function update(Request $request, $id)
{
    $employer = auth()->user()->employer;


    $job = $employer->jobListings()->findOrFail($id);


    $validated = $request->validate([
        'title' => 'required|string',
        'description' => 'nullable|string',
        'location' => 'required|string',
        'type' => 'required|string',
        'slots' => 'required|integer|min:1',
        'closing_date' => ['required', 'date', 'after_or_equal:today'],
    ]);


    $job->update($validated);


    return redirect()
        ->route('employer.jobs.index')
        ->with('success', 'Job updated successfully!');
}


    // Delete job
   public function destroy($id)
{
    $employer = auth()->user()->employer;


    $job = $employer->jobListings()->findOrFail($id);


    $job->delete();


    return redirect()
        ->route('employer.jobs.index')
        ->with('success', 'Job deleted successfully!');
}


public function show(JobListing $job)
{
    return Inertia::render('Employer/Applicants', [
        'jobId' => $job->id
    ]);
}
}
