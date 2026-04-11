<?php

namespace App\Exports;

use App\Models\WarehouseStockLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromView;

class HutangReportExport implements FromView
{
    protected $start;
    protected $end;
    protected $supplierName;
    protected $tempo;
    protected $status;
    protected $search;

    public function __construct($start = null, $end = null, $supplierName = null, $tempo = null, $status = null, $search = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->supplierName = $supplierName;
        $this->tempo = $tempo;
        $this->status = $status;
        $this->search = $search;
    }

    public function view(): View
    {
        $query = DB::table('warehouse_stock_logs')
            ->leftJoin('cash_flows', function ($join) {
                $join->on('cash_flows.reference_id', '=', 'warehouse_stock_logs.id')
                     ->where('cash_flows.reference_type', '=', 'warehouse')
                     ->where('cash_flows.type', '=', 'expense');
            })
            ->select([
                'warehouse_stock_logs.id',
                'warehouse_stock_logs.transaction_code',
                'warehouse_stock_logs.created_at as tanggal',
                'warehouse_stock_logs.supplier_name as supplier',
                'warehouse_stock_logs.due_date as tempo',
                'warehouse_stock_logs.total',
                DB::raw('COALESCE(SUM(cash_flows.amount), 0) as sudah_dibayar'),
                DB::raw('(warehouse_stock_logs.total - COALESCE(SUM(cash_flows.amount), 0)) as sisa_hutang')
            ])
            ->groupBy('warehouse_stock_logs.id')
            ->when($this->status === 'lunas', fn ($q) => $q->havingRaw('(warehouse_stock_logs.total - COALESCE(SUM(cash_flows.amount), 0)) = 0'))
            ->when($this->status === 'belum' || !$this->status, fn ($q) => $q->havingRaw('(warehouse_stock_logs.total - COALESCE(SUM(cash_flows.amount), 0)) > 0'))
            ->when($this->start, fn ($q) => $q->whereDate('warehouse_stock_logs.created_at', '>=', $this->start))
            ->when($this->end, fn ($q) => $q->whereDate('warehouse_stock_logs.created_at', '<=', $this->end))
            ->when($this->supplierName, fn ($q) => $q->where('warehouse_stock_logs.supplier_name', $this->supplierName))
            ->when($this->tempo, function ($q) {
                if ($this->tempo === 'net_30') {
                    $q->whereRaw("(due_date::date - created_at::date) <= 30")
                      ->whereRaw("(due_date::date - created_at::date) >= 0");
                } elseif ($this->tempo === 'net_60') {
                    $q->whereRaw("(due_date::date - created_at::date) > 30")
                      ->whereRaw("(due_date::date - created_at::date) <= 60");
                } elseif ($this->tempo === 'net_90') {
                    $q->whereRaw("(due_date::date - created_at::date) > 60")
                      ->whereRaw("(due_date::date - created_at::date) <= 90");
                } elseif ($this->tempo === 'sdt') {
                    $q->whereDate('due_date', '<', now());
                }
            })
            ->orderByDesc('warehouse_stock_logs.created_at');

        $items = $query->get();
        $totalHutang = $items->sum('sisa_hutang');

        return view('exports.hutang_report', [
            'items' => $items,
            'totalHutang' => $totalHutang,
            'start' => $this->start,
            'end' => $this->end,
            'supplierName' => $this->supplierName,
            'tempo' => $this->tempo,
        ]);
    }
}
