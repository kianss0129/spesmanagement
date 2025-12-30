<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Beneficiary;
use App\Models\Employer;
use App\Models\Application;
use Carbon\Carbon;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Admin/Dashboard');
    }

    public function stats(\Illuminate\Http\Request $request)
    {
        // Support a configurable window for growth charts via `days` query param (default 7)
        $days = max(1, intval($request->get('days', 7)));
        $dates = collect(range($days - 1, 0))->map(function($i) {
            return Carbon::today()->subDays($i)->toDateString();
        });

        $usersGrowth = $dates->map(fn($date) => User::whereDate('created_at', $date)->count());
        $beneficiariesGrowth = $dates->map(fn($date) => Beneficiary::whereDate('created_at', $date)->count());
        $employersGrowth = $dates->map(fn($date) => Employer::whereDate('created_at', $date)->count());

        // Guard against schema differences: applications don't store a `peso_id` column in this schema
        // so derive a sensible default (empty collection) and optionally collect activity-based counts.
        $applicationsByPESO = collect();

        try {
            // If Activity Log table exists and Spatie activity package is available, use it to count assignments by causer (PESO)
            $activityExists = \Schema::hasTable('activity_log');
            if ($activityExists) {
                $raw = \DB::table('activity_log')
                    ->selectRaw('causer_id, COUNT(*) as total')
                    ->where('description', 'like', '%PESO assigned beneficiary%')
                    ->groupBy('causer_id')
                    ->orderByDesc('total')
                    ->get();
                $applicationsByPESO = $raw;
            }
        } catch (\Throwable $e) {
            // ignore and keep empty collection
        }

        // role-aware counts (Spatie roles)
        $pesoCount = 0;
        try {
            $pesoCount = \Spatie\Permission\Models\Role::where('name', 'PESO')->exists() ? \Spatie\Permission\Models\Role::findByName('PESO')->users()->count() : 0;
        } catch (\Throwable $e) {
            $pesoCount = 0;
        }

        // Recent activity (best-effort) with causer name if available
        $recentActivity = [];
        try {
            if (\Schema::hasTable('activity_log')) {
                $recentActivity = \DB::table('activity_log')
                    ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
                    ->select('activity_log.id','activity_log.description','activity_log.causer_id','users.name as causer_name','activity_log.created_at')
                    ->orderBy('activity_log.created_at', 'desc')
                    ->limit(10)
                    ->get();
            }
        } catch (\Throwable $e) {
            $recentActivity = [];
        }

        // Counts: assigned beneficiaries, upcoming interviews, pending applications
        $assigned_beneficiaries = 0;
        try {
            if (\Schema::hasTable('job_listings') && \Schema::hasColumn('job_listings', 'assigned_beneficiary_id')) {
                $assigned_beneficiaries = \App\Models\JobListing::whereNotNull('assigned_beneficiary_id')->count();
            }
        } catch (\Throwable $e) {
            $assigned_beneficiaries = 0;
        }

        $upcoming_interviews = 0;
        try {
            if (\Schema::hasTable('interviews')) {
                $upcoming_interviews = \App\Models\Interview::where('status', 'scheduled')->where('scheduled_at', '>=', now())->count();
            }
        } catch (\Throwable $e) {
            $upcoming_interviews = 0;
        }

        $pending_applications = 0;
        try {
            if (\Schema::hasTable('applications')) {
                $pending_applications = \App\Models\Application::where('status', '!=', 'completed')->count();
            }
        } catch (\Throwable $e) {
            $pending_applications = 0;
        }

        return response()->json([
            'users' => User::count(),
            'beneficiaries' => Beneficiary::count(),
            'employers' => Employer::count(),
            'peso_users' => $pesoCount,
            'assigned_beneficiaries' => $assigned_beneficiaries,
            'upcoming_interviews' => $upcoming_interviews,
            'pending_applications' => $pending_applications,
            'latest_users' => User::orderBy('created_at', 'desc')->take(5)->get(['id','name','email']),
            'chart_dates' => $dates,
            'users_growth' => $usersGrowth,
            'beneficiaries_growth' => $beneficiariesGrowth,
            'employers_growth' => $employersGrowth,
            'applications_by_peso' => $applicationsByPESO,
            'recent_activity' => $recentActivity,
        ]);
    }

    // Stream user list as CSV for export
    public function exportUsers()
    {
        $filename = 'users-export-'.now()->format('YmdHis').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            // Header
            fputcsv($file, ['id','name','email','roles','created_at']);

            \App\Models\User::with('roles')->chunk(200, function($users) use ($file){
                foreach($users as $u){
                    $roles = $u->getRoleNames()->join(',');
                    fputcsv($file, [$u->id, $u->name, $u->email, $roles, $u->created_at]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}   