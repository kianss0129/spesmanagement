<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\Employer;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function beneficiaries()
    {
        return Beneficiary::latest()->get();
    }

    public function employers()
    {
        return Employer::latest()->get();
    }

    public function dashboard()
    {
        return Inertia::render('Admin/Dashboard');
    }
}
