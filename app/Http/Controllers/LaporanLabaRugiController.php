<?php

namespace App\Http\Controllers;

use App\Exports\ProfitLossReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LaporanLabaRugiController extends Controller
{
    private function getProfitLossData($start = null, $end = null)
    {
        $base = DB::table('accounting')
            ->join('accounting_details', 'accounting.id', '=', 'accounting_details.accounting_id')
            ->join('accounts', 'accounts.id', '=', 'accounting_details.account_id')
            ->whereIn('accounting.status', ['posted', 'posting'])
            ->when($start, fn ($q) => $q->whereDate('accounting.transaction_date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('accounting.transaction_date', '<=', $end));

        $penjualan = (clone $base)
            ->where(function ($q) {
                $q->where('accounts.type_account', 'revenue');
            })
            ->where(function ($q) {
                $q->whereRaw('LOWER(accounts.name) LIKE ?', ['%jual%'])
                    ->orWhereRaw('LOWER(accounts.name) LIKE ?', ['%penjualan%']);
            })
            ->selectRaw('COALESCE(SUM(accounting_details.credit), 0) - COALESCE(SUM(accounting_details.debit), 0) AS value')
            ->value('value') ?: 0;

        $pendapatanRevenues = (clone $base)
            ->where(function ($q) {
                $q->where('accounts.type_account', 'revenue');
            })
            ->selectRaw('COALESCE(SUM(accounting_details.credit), 0) - COALESCE(SUM(accounting_details.debit), 0) AS value')
            ->value('value') ?: 0;

        $pendapatanLain = $pendapatanRevenues - $penjualan;

        $hpp = (clone $base)
            ->where(function ($q) {
                $q->where('accounts.type_account', 'cogs')
                    ->orWhere('accounts.type_account', 'cost_of_goods_sold');
            })
            ->selectRaw('COALESCE(SUM(accounting_details.debit), 0) - COALESCE(SUM(accounting_details.credit), 0) AS value')
            ->value('value') ?: 0;

        $bebanOperasional = (clone $base)
            ->where(function ($q) {
                $q->where('accounts.type_account', 'expense');
            })
            ->where(function ($q) {
                $q->whereRaw('LOWER(accounts.name) LIKE ?', ['%operasional%'])
                    ->orWhereRaw('LOWER(accounts.name) LIKE ?', ['%beban%']);
            })
            ->selectRaw('COALESCE(SUM(accounting_details.debit), 0) - COALESCE(SUM(accounting_details.credit), 0) AS value')
            ->value('value') ?: 0;

        $bebanTotal = (clone $base)
            ->where(function ($q) {
                $q->where('accounts.type_account', 'expense');
            })
            ->selectRaw('COALESCE(SUM(accounting_details.debit), 0) - COALESCE(SUM(accounting_details.credit), 0) AS value')
            ->value('value') ?: 0;

        $bebanLain = $bebanTotal - $bebanOperasional;

        $totalPendapatan = $penjualan + $pendapatanLain;
        $labaKotor = $totalPendapatan - $hpp;
        $labaBersih = $labaKotor - $bebanOperasional - $bebanLain;

        return [
            'penjualan' => (float) $penjualan,
            'pendapatan_lain' => (float) $pendapatanLain,
            'hpp' => (float) $hpp,
            'beban_operasional' => (float) $bebanOperasional,
            'beban_lain' => (float) $bebanLain,
            'laba_kotor' => (float) $labaKotor,
            'laba_bersih' => (float) $labaBersih,
        ];
    }

    public function index(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $perPage = $request->query('per_page', '10');

        $data = $this->getProfitLossData($start, $end);
        $items = collect([
            ['name' => 'Pendapatan Penjualan', 'value' => $data['penjualan']],
            ['name' => 'Pendapatan Lain', 'value' => $data['pendapatan_lain']],
            ['name' => 'Total Pendapatan', 'value' => $data['penjualan'] + $data['pendapatan_lain']],
            ['name' => 'HPP', 'value' => $data['hpp']],
            ['name' => 'Laba Kotor', 'value' => $data['laba_kotor']],
            ['name' => 'Beban Operasional', 'value' => $data['beban_operasional']],
            ['name' => 'Beban Lain', 'value' => $data['beban_lain']],
            ['name' => 'Laba Bersih', 'value' => $data['laba_bersih']],
        ]);

        if ($perPage === 'all') {
            $paginated = new LengthAwarePaginator($items, $items->count(), $items->count(), 1, ['path' => LengthAwarePaginator::resolveCurrentPath()]);
        } else {
            $perPageNumber = max(1, (int) $perPage);
            $currentPage = $request->query('page', 1);
            $paginated = new LengthAwarePaginator($items->forPage($currentPage, $perPageNumber), $items->count(), $perPageNumber, $currentPage, ['path' => LengthAwarePaginator::resolveCurrentPath()]);
        }
        $paginated->appends($request->query());

        return view('umkm.laporan_laba_rugi', array_merge($data, [
            'pendapatanLain' => $data['pendapatan_lain'],
            'labaKotor' => $data['laba_kotor'],
            'labaBersih' => $data['laba_bersih'],
            'bebanOperasional' => $data['beban_operasional'],
            'bebanLain' => $data['beban_lain'],
            'items' => $paginated,
            'start' => $start,
            'end' => $end,
            'perPage' => $perPage,
        ]));
    }

    public function exportExcel(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        return Excel::download(new ProfitLossReportExport($start, $end), 'laporan-laba-rugi.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        $data = $this->getProfitLossData($start, $end);

        $pdf = Pdf::loadView('pdf.laba_rugi_report', array_merge($data, [
            'pendapatanLain' => $data['pendapatan_lain'],
            'labaKotor' => $data['laba_kotor'],
            'labaBersih' => $data['laba_bersih'],
            'bebanOperasional' => $data['beban_operasional'],
            'bebanLain' => $data['beban_lain'],
            'start' => $start,
            'end' => $end,
        ]));

        return $pdf->download('laporan-laba-rugi.pdf');
    }
}
