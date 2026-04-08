<?php

namespace App\Exports;

use App\Services\CashFlowReportService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ArusKasExport implements FromView
{
    private $start;
    private $end;

    public function __construct(?string $start, ?string $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function view(): View
    {
        $report = CashFlowReportService::buildReportData($this->start, $this->end);

        return view('exports.arus_kas', [
            'report' => $report,
        ]);
    }
}
