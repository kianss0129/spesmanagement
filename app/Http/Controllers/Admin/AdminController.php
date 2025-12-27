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

    public function stats()
    {
        // Last 7 days for growth charts
        $dates = collect(range(6, 0))->map(function($i) {
            return Carbon::today()->subDays($i)->toDateString();
        });

        $usersGrowth = $dates->map(fn($date) => User::whereDate('created_at', $date)->count());
        $beneficiariesGrowth = $dates->map(fn($date) => Beneficiary::whereDate('created_at', $date)->count());
        $employersGrowth = $dates->map(fn($date) => Employer::whereDate('created_at', $date)->count());

        $applicationsByPESO = Application::selectRaw('peso_id, count(*) as total')
            ->groupBy('peso_id')
            ->get();

        return response()->json([
            'users' => User::count(),
            'beneficiaries' => Beneficiary::count(),
            'employers' => Employer::count(),
            'latest_users' => User::orderBy('created_at', 'desc')->take(5)->get(['id','name','email']),
            'chart_dates' => $dates,
            'users_growth' => $usersGrowth,
            'beneficiaries_growth' => $beneficiariesGrowth,
            'employers_growth' => $employersGrowth,
            'applications_by_peso' => $applicationsByPESO
        ]);
    }
}   