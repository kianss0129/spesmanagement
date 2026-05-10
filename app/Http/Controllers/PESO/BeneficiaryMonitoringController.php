<?php

namespace App\Http\Controllers\PESO;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;

class BeneficiaryMonitoringController extends Controller
{
    public function index()
    {
        $beneficiaries = Beneficiary::all()->map(function($b) {
            return [
                'id' => $b->id,
                'name' => $b->name,
                'status' => ucfirst($b->approval_status), // Pending / Approved / Rejected
                'assigned_employer' => $b->assigned_employer?->name ?? null,
            ];
        });

        return response()->json($beneficiaries);
    }
}
