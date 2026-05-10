<?php

namespace App\Http\Controllers\PESO;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    // 🔹 Update contract result (SIGNED / NOT SIGNED)
    public function updateResult(Request $request, $id)
    {
        $validated = $request->validate([
            'result' => 'required|in:signed,not_signed'
        ]);

        $contract = Contract::with('application')->findOrFail($id);

        // 🚨 جلوگیری re-processing
        if ($contract->status !== 'scheduled') {
            return response()->json([
                'message' => 'Contract already processed.'
            ], 400);
        }

        DB::transaction(function () use ($contract, $validated) {
            $contract->update([
                'status' => 'completed',
                'result' => $validated['result']
            ]);

            if ($validated['result'] === 'signed') {
                $contract->application->update([
                    'status' => 'completed'
                ]);
            } else {
                $contract->application->update([
                    'status' => 'rejected'
                ]);
            }
        });

        return response()->json([
            'message' => 'Contract result updated successfully'
        ]);
    }

    // 🔹 Schedule contract
    public function store(Request $request)
    {
        $validated = $request->validate([
            'application_id' => 'required',
            'contract_date' => 'required|date|after_or_equal:now',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $applicationId = $validated['application_id'];

        // 🚨 Prevent duplicate scheduled contract
        $exists = Contract::where('application_id', $applicationId)
            ->where('status', 'scheduled')
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Application already has a scheduled contract.'
            ], 400);
        }

        $contract = Contract::create([
            'application_id' => $applicationId,
            'contract_date' => Carbon::parse($validated['contract_date']),
            'location' => $validated['location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'scheduled'
        ]);

        // 🔥 Update application status
        $contract->application->update([
            'status' => 'for_contract'
        ]);

        return response()->json([
            'message' => 'Contract scheduled successfully'
        ]);
    }

    // 🔹 Admin / dashboard upcoming contracts
    public function upcomingContracts()
    {
        $contracts = Contract::with('application.beneficiary')
            ->where('contract_date', '>=', Carbon::now())
            ->orderBy('contract_date', 'asc')
            ->take(5)
            ->get()
            ->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'application_id' => $contract->application_id,
                    'beneficiary_name' => $contract->application ? trim(
                        ($contract->application->beneficiary->first_name ?? '') . ' ' .
                        ($contract->application->beneficiary->last_name ?? '')
                    ) ?: 'N/A' : 'N/A',
                    'contract_date' => $contract->contract_date,
                    'location' => $contract->location ?? 'TBA',
                    'status' => $contract->status,
                    'result' => $contract->result,
                ];
            });

        return response()->json($contracts);
    }
}
