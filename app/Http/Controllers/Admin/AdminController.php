<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Beneficiary;
use App\Models\Employer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Admin/Dashboard');
    }

    public function stats(Request $request)
    {
        // Days filter for charts
        $days = max(1, (int) $request->get('days', 7));

        $dates = collect(range($days - 1, 0))->map(fn ($i) =>
            Carbon::today()->subDays($i)->toDateString()
        );

        // Growth charts
        $usersGrowth = $dates->map(fn ($date) => User::whereDate('created_at', $date)->count());
        $beneficiariesGrowth = $dates->map(fn ($date) => Beneficiary::whereDate('created_at', $date)->count());
        $employersGrowth = $dates->map(fn ($date) => Employer::whereDate('created_at', $date)->count());

        // Pending counts
        $pendingBeneficiaries = Beneficiary::where('status', 'pending')->count();
        $pendingEmployers     = Employer::where('approved', false)->count();
        $pendingApplications  = $pendingBeneficiaries + $pendingEmployers;

        // PESO users count safely
        $pesoUsers = 0;
        try {
            if (Role::where('name', 'PESO')->exists()) {
                $pesoUsers = Role::findByName('PESO')->users()->count();
            }
        } catch (\Throwable $e) {
            $pesoUsers = 0; // fallback if role/table missing
        }

        // Recent Activity safely
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

        return response()->json([
            // Stats cards
            'users'                  => User::count(),
            'beneficiaries'          => Beneficiary::count(),
            'employers'              => Employer::count(),
            'peso_users'             => $pesoUsers,

            // Pending applications
            'pending_beneficiaries'  => $pendingBeneficiaries,
            'pending_employers'      => $pendingEmployers,
            'pending_applications'   => $pendingApplications,

            // Other dashboard data
            'assigned_beneficiaries' => 0,
            'upcoming_interviews'    => 0,

            // Tables & charts
            'latest_users'           => User::latest()->take(5)->get(['id', 'name', 'email']),
            'chart_dates'            => $dates,
            'users_growth'           => $usersGrowth,
            'beneficiaries_growth'   => $beneficiariesGrowth,
            'employers_growth'       => $employersGrowth,
            'applications_by_peso'   => [],
            'recent_activity'        => $recentActivity,
        ]);
    }

    public function exportUsers()
    {
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
