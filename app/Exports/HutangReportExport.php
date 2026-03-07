<?php

namespace App\Exports;

use App\Models\WarehouseStockTransaction;
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
        $transactionDateColumn = Schema::hasColumn('warehouse_stock_transactions', 'created_at')
            ? 'created_at'
            : '';

        $paidColumn = Schema::hasColumn('warehouse_stock_transactions', 'amount_paid')
            ? 'amount_paid'
            : 'paid';

        $query = WarehouseStockTransaction::query()
            ->where('action_type', 'add')
            ->when($this->status === 'lunas', fn ($q) => $q->where('remaining', '=', 0))
            ->when($this->status === 'belum' || !$this->status, fn ($q) => $q->where('remaining', '>', 0))
            ->when($this->start, fn ($q) => $q->whereDate($transactionDateColumn, '>=', $this->start))
            ->when($this->end, fn ($q) => $q->whereDate($transactionDateColumn, '<=', $this->end))
            ->when($this->supplierName, fn ($q) => $q->where('supplier_name', $this->supplierName))
            ->when($this->tempo, function ($q) use ($transactionDateColumn) {
                if ($this->tempo === 'net_30') {
                    $q->whereRaw("(due_date::date - {$transactionDateColumn}::date) <= 30")
                    ->whereRaw("(due_date::date - {$transactionDateColumn}::date) >= 0");
                }
                elseif ($this->tempo === 'net_60') {
                    $q->whereRaw("(due_date::date - {$transactionDateColumn}::date) > 30")
                    ->whereRaw("(due_date::date - {$transactionDateColumn}::date) <= 60");
                }
                elseif ($this->tempo === 'net_90') {
                    $q->whereRaw("(due_date::date - {$transactionDateColumn}::date) > 60")
                    ->whereRaw("(due_date::date - {$transactionDateColumn}::date) <= 90");
                }
                elseif ($this->tempo === 'sdt') {
                    $q->whereDate('due_date', '<', now());
                }
            })
            ->orderByDesc($transactionDateColumn);

        $items = $query->get();
        $totalHutang = $items->sum('remaining');

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
