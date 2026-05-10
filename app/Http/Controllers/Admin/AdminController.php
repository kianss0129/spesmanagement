<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Beneficiary;
use App\Models\Employer;
use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use App\Models\Application;
use App\Models\Interview;

class AdminController extends Controller
{



 public function notifications()
{
    $announcements = Announcement::latest()->get();

    return Inertia::render('PESO/Notifications', [
        'announcements' => $announcements
    ]);
}

    public function settings()
{
   $sessions = DB::table('sessions')
    ->where('user_id', auth()->id())
    ->orderByDesc('last_activity')
    ->get();
    return Inertia::render('Admin/Settings', [
        'sessions' => $sessions
    ]);
}

    public function dashboard()
    {
        return Inertia::render('Admin/Dashboard');
    }

    public function getStatsForDashboard()
    {
        return $this->stats(request());
    }

    public function stats(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['Admin', 'PESO Admin'])) {
            abort(403, 'Unauthorized');
        }

        // Days filter for charts
        $days = max(1, (int) $request->get('days', 7));

        $dates = collect(range($days - 1, 0))->map(function ($i) {
            return Carbon::today()->subDays($i)->toDateString();
        });

        /*
        |--------------------------------------------------------------------------
        | Growth Charts (Daily Created Records)
        |--------------------------------------------------------------------------
        */

        $usersGrowth = $dates->map(function ($date) {
            return User::whereDate('created_at', $date)->count();
        });

        $beneficiariesGrowth = $dates->map(function ($date) {
            return Beneficiary::whereDate('created_at', $date)->count();
        });

        $employersGrowth = $dates->map(function ($date) {
            return Employer::whereDate('created_at', $date)->count();
        });

        /*
        |--------------------------------------------------------------------------
        | Pending Counts (FIXED & CONSISTENT)
        |--------------------------------------------------------------------------
        */

        // ONLY use approval_status for beneficiaries
        $pendingBeneficiaries = Beneficiary::where('approval_status', 'pending')->count();

        // Employers pending (assuming approved = false means pending)
        $pendingEmployers = Employer::where('approved', false)->count();

        $pendingApplications = $pendingBeneficiaries + $pendingEmployers;

        /*
        |--------------------------------------------------------------------------
        | Total Counts (OPTIONAL: approved-only logic)
        |--------------------------------------------------------------------------
        */

        $totalBeneficiaries = Beneficiary::count();
        // If you want approved only, use this instead:
        // $totalBeneficiaries = Beneficiary::where('approval_status', 'approved')->count();

        $totalEmployers = Employer::count();

        /*
        |--------------------------------------------------------------------------
        | PESO Users Count
        |--------------------------------------------------------------------------
        */

        $pesoUsers = 0;
        try {
            if (Role::where('name', 'PESO')->exists()) {
                $pesoUsers = Role::findByName('PESO')->users()->count();
            }
        } catch (\Throwable $e) {
            $pesoUsers = 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Recent Activity (Safe Check)
        |--------------------------------------------------------------------------
        */

        $recentActivity = [];

        if (Schema::hasTable('activity_log')) {
            $recentActivity = DB::table('activity_log')
                ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
                ->select(
                    'activity_log.id',
                    'activity_log.description',
                    'activity_log.causer_id',
                    'users.name as causer_name',
                    'activity_log.created_at'
                )
                ->latest('activity_log.created_at')
                ->limit(10)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Final Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            // Main stats
            'users'                  => User::count(),
            'beneficiaries'          => $totalBeneficiaries,
            'employers'              => $totalEmployers,
            'peso_users'             => $pesoUsers,

            // Pending
            'pending_beneficiaries'  => $pendingBeneficiaries,
            'pending_employers'      => $pendingEmployers,
            'pending_applications'   => $pendingApplications,

            // Other dashboard placeholders
          'assigned_beneficiaries' => Application::where('status', 'assigned')->count(),

'upcoming_interviews' => Interview::where('scheduled_at', '>=', now())->count(),

            // Tables
            'latest_users'           => User::latest()
                                            ->take(5)
                                            ->get(['id', 'name', 'email']),

            // Charts
            'chart_dates'            => $dates,
            'users_growth'           => $usersGrowth,
            'beneficiaries_growth'   => $beneficiariesGrowth,
            'employers_growth'       => $employersGrowth,
            'applications_by_peso'   => [],

            // Activity
            'recent_activity'        => $recentActivity,
        ]);
    }

    public function exportUsers()
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized');
        }

        $filename = 'users-' . now()->format('YmdHis') . '.csv';

        return response()->stream(function () {

            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Name', 'Email', 'Roles', 'Created At']);

            User::with('roles')->chunk(200, function ($users) use ($file) {

                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->getRoleNames()->join(','),
                        $user->created_at,
                    ]);
                }

            });

            fclose($file);

        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
