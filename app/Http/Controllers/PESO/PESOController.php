<?php

namespace App\Http\Controllers\PESO;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Models\JobListing;
use App\Models\Beneficiary;
use App\Models\Employer;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class PESOController extends Controller
{
    /**
     * PESO Dashboard
     */
    public function dashboard()
    {
        return Inertia::render('PESO/Dashboard');
    }

    /**
     * ==========================
     * PENDING BENEFICIARIES
     * ==========================
     */
    public function pendingBeneficiaries()
    {
        $beneficiaries = Beneficiary::with('user')
            ->whereNotNull('onboarding_completed_at')
            ->where('approved', false)
            ->get();

        return Inertia::render('PESO/PendingBeneficiaries', [
            'beneficiaries' => $beneficiaries,
            'canApprove' => Auth::user()->hasAnyRole(['PESO Admin', 'Admin']),
        ]);
    }

    public function monitoring()
    {
        $beneficiaries = Beneficiary::with(['user', 'applications.jobListing.employer'])
            ->where('approved', true)
            ->get()
            ->map(function ($beneficiary) {
                $latestApplication = $beneficiary->applications->sortByDesc('created_at')->first();
                $status = $latestApplication ? $latestApplication->status : 'No Application';
                $assignedEmployer = $latestApplication && $latestApplication->jobListing ? $latestApplication->jobListing->employer->company_name : null;

                return [
                    'id' => $beneficiary->id,
                    'name' => $beneficiary->name,
                    'status' => $status,
                    'assigned_employer' => $assignedEmployer,
                ];
            });

        return response()->json($beneficiaries);
    }

    public function approveBeneficiary($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);

        $beneficiary->update([
            'approved' => true,
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // Assign beneficiary role to the user
        $beneficiary->user()->update(['role' => 'beneficiary']);

        return back()->with('success', 'Beneficiary approved');
    }

    public function rejectBeneficiary($id, Request $request)
    {
        $beneficiary = Beneficiary::findOrFail($id);

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $beneficiary->update([
            'approved' => false,
            'approval_status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => Auth::id(),
        ]);

        return back()->with('error', 'Beneficiary rejected');
    }

    /**
     * ==========================
     * PENDING EMPLOYERS
     * ==========================
     */
    public function pendingEmployers()
    {
        $employers = Employer::with('user')
            ->where('approved', false)
            ->get();

        return Inertia::render('PESO/Employers/Pending', [
            'employers' => $employers,
            'canApprove' => Auth::user()->hasAnyRole(['PESO Admin', 'Admin']),
        ]);
    }

    public function approveEmployer($id)
    {
        $employer = Employer::findOrFail($id);

        $employer->update([
            'approved' => true,
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // Assign employer role to the user
        $employer->user()->update(['role' => 'employer']);

        return back()->with('success', 'Employer approved');
    }

    public function rejectEmployer($id, Request $request)
    {
        $employer = Employer::findOrFail($id);

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $employer->update([
            'approved' => false,
            'approval_status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => Auth::id(),
        ]);

        return back()->with('error', 'Employer rejected');
    }

    /**
     * ==========================
     * ONBOARDING VERIFICATION
     * ==========================
     */
    public function viewBeneficiaryApplications(Beneficiary $beneficiary)
    {
        // This now shows onboarding verification instead of job applications
        $beneficiary->load('user', 'school');

        // Build documents array from stored file paths (if any)
        $documents = [];
        if ($beneficiary->documents) {
            $raw = is_array($beneficiary->documents) ? $beneficiary->documents : [$beneficiary->documents];
            foreach ($raw as $entry) {
                // Entry may be a plain path string, or an array/object with keys like 'path','name','uploaded_at'
                $entryPath = null;
                $entryName = null;
                $entryUploadedAt = null;

                if (is_string($entry)) {
                    $entryPath = $entry;
                } elseif (is_array($entry)) {
                    $entryPath = $entry['path'] ?? $entry['file'] ?? null;
                    $entryName = $entry['name'] ?? $entry['filename'] ?? null;
                    $entryUploadedAt = $entry['uploaded_at'] ?? $entry['created_at'] ?? null;
                } elseif (is_object($entry)) {
                    $entryPath = $entry->path ?? $entry->file ?? null;
                    $entryName = $entry->name ?? $entry->filename ?? null;
                    $entryUploadedAt = $entry->uploaded_at ?? $entry->created_at ?? null;
                }

                if (! $entryPath) {
                    // skip malformed entry
                    continue;
                }

                // Check if file exists on public disk
                $exists = Storage::disk('public')->exists((string) $entryPath);

                // Build URL for storage path using Storage disk
                $url = null;
                if ($exists) {
                    try {
                        $url = Storage::disk('public')->url((string) $entryPath);
                    } catch (\Throwable $e) {
                        $url = null;
                    }
                }

                // Determine uploaded at timestamp from file system if not provided
                $uploadedAt = $entryUploadedAt;
                if (! $uploadedAt && $exists) {
                    try {
                        $fullPath = storage_path('app/public/' . ltrim((string) $entryPath, '/'));
                        if (file_exists($fullPath)) {
                            $uploadedAt = date('c', filemtime($fullPath));
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                $documents[] = [
                    'path' => $entryPath,
                    'url' => $url,
                    'name' => $entryName ?? basename((string) $entryPath),
                    'uploaded_at' => $uploadedAt,
                    'exists' => $exists,
                ];
            }
        }

        return Inertia::render('PESO/Beneficiaries/Applications', [
            'beneficiary' => $beneficiary,
            'documents' => $documents,
            'submission_date' => $beneficiary->onboarding_completed_at,
            'approval_status' => $beneficiary->approval_status ?? 'pending',
            'rejection_reason' => $beneficiary->rejection_reason,
        ]);
    }

    public function viewEmployerApplications(Employer $employer)
    {
        // This now shows onboarding verification instead of job applications
        $employer->load('user');

        // Build documents array from stored file paths (if any)
        $documents = [];
        if ($employer->documents) {
            $raw = is_array($employer->documents) ? $employer->documents : [$employer->documents];
            foreach ($raw as $entry) {
                // Entry may be a plain path string, or an array/object with keys like 'path','name','uploaded_at'
                $entryPath = null;
                $entryName = null;
                $entryUploadedAt = null;

                if (is_string($entry)) {
                    $entryPath = $entry;
                } elseif (is_array($entry)) {
                    $entryPath = $entry['path'] ?? $entry['file'] ?? null;
                    $entryName = $entry['name'] ?? $entry['filename'] ?? null;
                    $entryUploadedAt = $entry['uploaded_at'] ?? $entry['created_at'] ?? null;
                } elseif (is_object($entry)) {
                    $entryPath = $entry->path ?? $entry->file ?? null;
                    $entryName = $entry->name ?? $entry->filename ?? null;
                    $entryUploadedAt = $entry->uploaded_at ?? $entry->created_at ?? null;
                }

                if (! $entryPath) {
                    // skip malformed entry
                    continue;
                }

                // Check if file exists on public disk
                $exists = Storage::disk('public')->exists((string) $entryPath);

                // Build URL for storage path using Storage disk
                $url = null;
                if ($exists) {
                    try {
                        $url = Storage::disk('public')->url((string) $entryPath);
                    } catch (\Throwable $e) {
                        $url = null;
                    }
                }

                // Determine uploaded at timestamp from file system if not provided
                $uploadedAt = $entryUploadedAt;
                if (! $uploadedAt && $exists) {
                    try {
                        $fullPath = storage_path('app/public/' . ltrim((string) $entryPath, '/'));
                        if (file_exists($fullPath)) {
                            $uploadedAt = date('c', filemtime($fullPath));
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                $documents[] = [
                    'path' => $entryPath,
                    'url' => $url,
                    'name' => $entryName ?? basename((string) $entryPath),
                    'uploaded_at' => $uploadedAt,
                    'exists' => $exists,
                ];
            }
        }

        return Inertia::render('PESO/Employers/Applications', [
            'employer' => $employer,
            'documents' => $documents,
            'submission_date' => $employer->onboarding_completed_at,
            'company_details' => [
                'company_name' => $employer->company_name ?? 'N/A',
                'contact_person' => $employer->contact_person ?? 'N/A',
                'email' => $employer->email ?? 'N/A',
                'phone' => $employer->phone ?? 'N/A',
                'address' => $employer->address ?? 'N/A',
            ],
            'approval_status' => $employer->approval_status ?? 'pending',
            'rejection_reason' => $employer->rejection_reason,
        ]);
    }

    public function jobListings()
    {
        $jobListings = JobListing::with(['employer', 'applications'])
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'employer_name' => $job->employer->company_name,
                    'applications_count' => $job->applications->count(),
                    'status' => $job->status,
                ];
            });

        return response()->json($jobListings);
    }
}
