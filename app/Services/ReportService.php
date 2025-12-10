<?php

namespace App\Services;

use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CollectionExport;

class ReportService
{
    /**
     * Generate PDF report for DOLE/SPES
     */
    public function generateDOLEReport($data, $view = 'reports.dole', $filename = 'dole-report.pdf')
    {
        $pdf = PDF::loadView($view, compact('data'));
        return $pdf->download($filename);
    }

    /**
     * Export collection to Excel
     */
    public function exportExcel($collection, $filename = 'report.xlsx')
    {
        return Excel::download(new CollectionExport($collection), $filename);
    }
}
