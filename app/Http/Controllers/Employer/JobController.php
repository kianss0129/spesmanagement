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
        $jobs = JobListing::where('employer_id', auth()->id())->get();

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
            'closing_date' => 'required|date',
        ]);

        $validated['employer_id'] = auth()->id();

        JobListing::create($validated);

        return redirect()->route('employer.jobs.index')->with('success', 'Job posted successfully!');
    }

    // Edit job
    public function edit($id)
    {
        $job = JobListing::where('employer_id', auth()->id())->findOrFail($id);

        return Inertia::render('Employer/EditJob', [
            'job' => $job
        ]);
    }

    // Update job
    public function update(Request $request, $id)
    {
        $job = JobListing::where('employer_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'location' => 'required|string',
            'type' => 'required|string',
            'slots' => 'required|integer',
            'closing_date' => 'required|date',
        ]);

        $job->update($validated);

        return redirect()->route('employer.jobs.index')->with('success', 'Job updated successfully!');
    }

    // Delete job
    public function destroy($id)
    {
        $job = JobListing::where('employer_id', auth()->id())->findOrFail($id);

        $job->delete();

        return redirect()->route('employer.jobs.index')->with('success', 'Job deleted successfully!');
    }
}
