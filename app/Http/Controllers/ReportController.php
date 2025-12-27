<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Redirect;

class ReportController extends Controller
{
    /**
     * Generate a PDF report for appointments.
     */
    public function appointmentPDF()
    {
        $user = auth()->user();

        // Safety check
        if (! $user) {
            return Redirect::route('login');
        }

        /**
         * ROLE ACCESS RULES:
         * - admin → all appointments
         * - peso → all appointments
         * - employer → only employer-related appointments
         * - beneficiary → NOT allowed
         */

        if ($user->hasRole(['admin', 'peso'])) {

            $appointments = Appointment::all();

        } elseif ($user->hasRole('employer')) {

            // Adjust column name if different
            $appointments = Appointment::where('employer_id', $user->id)->get();

        } else {
            // beneficiary or unknown role
            return Redirect::route('dashboard');
        }

        // Generate PDF
        $pdf = Pdf::loadView('reports.appointments', compact('appointments'));

        return $pdf->download('appointments_report.pdf');
    }
}
