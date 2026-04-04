<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromView;

class ProfitLossReportExport implements FromView
{
    protected $start;
    protected $end;

    public function __construct($start = null, $end = null)
    {
        $this->start = $start;
        $this->end = $end;
    }

    private function queryBase()
    {
        return DB::table('accountings')
            ->join('accounting_details', 'accountings.id', '=', 'accounting_details.accounting_id')
            ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'accounting_details.coa_id')
            ->where('accountings.status_accounting', 'posting')
            ->when($this->start, fn ($q) => $q->whereDate('accountings.created_date', '>=', $this->start))
            ->when($this->end, fn ($q) => $q->whereDate('accountings.created_date', '<=', $this->end));
    }

    public function view(): View
    {
        $base = $this->queryBase();

        $penjualan = (clone $base)
            ->where('chart_of_accounts.type', 'revenue')
            ->where(function ($q) {
                $q->whereRaw('LOWER(chart_of_accounts.name) LIKE ?', ['%jual%'])
                    ->orWhereRaw('LOWER(chart_of_accounts.name) LIKE ?', ['%penjualan%']);
            })
            ->selectRaw('COALESCE(SUM(accounting_details.credit), 0) - COALESCE(SUM(accounting_details.debit), 0) AS value')
            ->value('value') ?: 0;

        $revenueTotal = (clone $base)
            ->where('chart_of_accounts.type', 'revenue')
            ->selectRaw('COALESCE(SUM(accounting_details.credit), 0) - COALESCE(SUM(accounting_details.debit), 0) AS value')
            ->value('value') ?: 0;

        $pendapatanLain = $revenueTotal - $penjualan;

        $hpp = (clone $base)
            ->where('chart_of_accounts.type', 'expense')
            ->whereRaw('LOWER(chart_of_accounts.name) LIKE ?', ['%hpp%'])
            ->selectRaw('COALESCE(SUM(accounting_details.debit),0) - COALESCE(SUM(accounting_details.credit),0) AS value')
            ->value('value') ?: 0;

        $bebanOperasional = (clone $base)
            ->where('chart_of_accounts.type', 'expense')
            ->where(function ($q) {
                $q->whereRaw('LOWER(chart_of_accounts.name) LIKE ?', ['%operasional%'])
                    ->orWhereRaw('LOWER(chart_of_accounts.name) LIKE ?', ['%beban%']);
            })
            ->selectRaw('COALESCE(SUM(accounting_details.debit),0) - COALESCE(SUM(accounting_details.credit),0) AS value')
            ->value('value') ?: 0;

        $bebanTotal = (clone $base)
            ->where('chart_of_accounts.type', 'expense')
            ->selectRaw('COALESCE(SUM(accounting_details.debit),0) - COALESCE(SUM(accounting_details.credit),0) AS value')
            ->value('value') ?: 0;

        $bebanLain = $bebanTotal - $bebanOperasional;
        $totalPendapatan = $penjualan + $pendapatanLain;
        $labaKotor = $totalPendapatan - $hpp;
        $labaBersih = $labaKotor - $bebanOperasional - $bebanLain;

        return view('exports.laba_rugi_report', [
            'start' => $this->start,
            'end' => $this->end,
            'penjualan' => $penjualan,
            'pendapatanLain' => $pendapatanLain,
            'hpp' => $hpp,
            'bebanOperasional' => $bebanOperasional,
            'bebanLain' => $bebanLain,
            'labaKotor' => $labaKotor,
            'labaBersih' => $labaBersih,
        ]);
    }
}
