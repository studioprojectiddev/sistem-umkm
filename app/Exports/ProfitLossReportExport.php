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
        return DB::table('accounting')
            ->join('accounting_details', 'accounting.id', '=', 'accounting_details.accounting_id')
            ->join('accounts', 'accounts.id', '=', 'accounting_details.account_id')
            ->whereIn('accounting.status', ['posted', 'posting'])
            ->when($this->start, fn ($q) => $q->whereDate('accounting.transaction_date', '>=', $this->start))
            ->when($this->end, fn ($q) => $q->whereDate('accounting.transaction_date', '<=', $this->end));
    }

    public function view(): View
    {
        $base = $this->queryBase();

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

        $revenueTotal = (clone $base)
            ->where(function ($q) {
                $q->where('accounts.type_account', 'revenue');
            })
            ->selectRaw('COALESCE(SUM(accounting_details.credit), 0) - COALESCE(SUM(accounting_details.debit), 0) AS value')
            ->value('value') ?: 0;

        $pendapatanLain = $revenueTotal - $penjualan;

        $hpp = (clone $base)
            ->where(function ($q) {
                $q->where('accounts.type_account', 'cogs')->orWhere('accounts.type_account', 'cost_of_goods_sold');
            })
            ->selectRaw('COALESCE(SUM(accounting_details.debit),0) - COALESCE(SUM(accounting_details.credit),0) AS value')
            ->value('value') ?: 0;

        $bebanOperasional = (clone $base)
            ->where(function ($q) {
                $q->where('accounts.type_account', 'expense');
            })
            ->where(function ($q) {
                $q->whereRaw('LOWER(accounts.name) LIKE ?', ['%operasional%'])
                    ->orWhereRaw('LOWER(accounts.name) LIKE ?', ['%beban%']);
            })
            ->selectRaw('COALESCE(SUM(accounting_details.debit),0) - COALESCE(SUM(accounting_details.credit),0) AS value')
            ->value('value') ?: 0;

        $bebanTotal = (clone $base)
            ->where(function ($q) {
                $q->where('accounts.type_account', 'expense');
            })
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
