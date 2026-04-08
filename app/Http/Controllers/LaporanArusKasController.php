<?php

namespace App\Http\Controllers;

use App\Exports\ArusKasExport;
use App\Services\CashFlowReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanArusKasController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        $report = CashFlowReportService::buildReportData($start, $end);

        return view('umkm.laporan_arus_kas', [
            'report' => $report,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        return Excel::download(new ArusKasExport($start, $end), 'laporan-arus-kas.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        $report = CashFlowReportService::buildReportData($start, $end);

        $pdf = Pdf::loadView('pdf.arus_kas_report', [
            'report' => $report,
        ]);

        return $pdf->download('laporan-arus-kas.pdf');
    }
}
