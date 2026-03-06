<?php

namespace App\Exports;

use App\Models\TransactionItem;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SalesReportExport implements FromView
{
    protected $start;
    protected $end;
    protected $search;
    protected $warehouseId;
    protected $status;

    public function __construct($start = null, $end = null, $search = null, $warehouseId = null, $status = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->search = $search;
        $this->warehouseId = $warehouseId;
        $this->status = $status;
    }

    public function view(): View
    {
        $query = TransactionItem::with(['transaction', 'product', 'variation'])
            ->when($this->start, fn ($q) => $q->whereHas('transaction', fn ($q) => $q->whereDate('transaction_date', '>=', $this->start)))
            ->when($this->end, fn ($q) => $q->whereHas('transaction', fn ($q) => $q->whereDate('transaction_date', '<=', $this->end)))
            ->when($this->warehouseId, fn ($q) => $q->whereHas('transaction', fn ($q) => $q->where('warehouse_id', $this->warehouseId)))
            ->when($this->status, fn ($q) => $q->whereHas('transaction', fn ($q) => $q->where('status', $this->status)))
            ->when($this->search, function ($q) {
                $search = $this->search;
                $q->whereHas('transaction', function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%");
                })
                ->orWhereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        $items = $query->get();
        $total = $items->sum('subtotal');

        return view('exports.sales_report', [
            'items' => $items,
            'total' => $total,
            'start' => $this->start,
            'end' => $this->end,
            'search' => $this->search,
        ]);
    }
}
