<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MonthlySummaryExport implements FromView
{
    public function __construct(
        public array  $report,
        public array  $leaveTypes,
        public string $monthLabel,
    ) {}

    public function view(): View
    {
        return view('reports.monthly-summary-excel', [
            'report'     => $this->report,
            'leaveTypes' => $this->leaveTypes,
            'monthLabel' => $this->monthLabel,
        ]);
    }
}
