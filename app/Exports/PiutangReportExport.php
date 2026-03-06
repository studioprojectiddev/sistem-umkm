<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PiutangReportExport implements FromView
{
    protected $start;
    protected $end;
    protected $customerName;
    protected $tempo;
    protected $status;
    protected $search;

    public function __construct($start = null, $end = null, $customerName = null, $tempo = null, $status = null, $search = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->customerName = $customerName;
        $this->tempo = $tempo;
        $this->status = $status;
        $this->search = $search;
    }

    public function view(): View
    {
        $query = Transaction::query()
            ->where('transaction_type', 'sale')
            ->when($this->start, fn ($q) => $q->whereDate('transaction_date', '>=', $this->start))
            ->when($this->end, fn ($q) => $q->whereDate('transaction_date', '<=', $this->end))
            ->when($this->customerName, fn ($q) => $q->where('customer_name', $this->customerName))
            ->when($this->tempo, function ($q) {
                if ($this->tempo === 'net_15') {
                    $q->whereRaw('(due_date::date - CURRENT_DATE) <= ?', [15]);
                } elseif ($this->tempo === 'net_30') {
                    $q->whereRaw('(due_date::date - CURRENT_DATE) BETWEEN ? AND ?', [16, 30]);
                } elseif ($this->tempo === 'net_60') {
                    $q->whereRaw('(due_date::date - CURRENT_DATE) BETWEEN ? AND ?', [31, 60]);
                } elseif ($this->tempo === 'net_90') {
                    $q->whereRaw('(due_date::date - CURRENT_DATE) BETWEEN ? AND ?', [61, 90]);
                } elseif ($this->tempo === 'sdt') {
                    $q->whereRaw('due_date::date < CURRENT_DATE');
                }
            })
            ->when($this->status === 'belum', fn ($q) => $q->whereRaw('total - COALESCE(uang_diterima,0) > 0'))
            ->when($this->status === 'lunas', fn ($q) => $q->whereRaw('total - COALESCE(uang_diterima,0) <= 0'))
            ->when(!$this->status, fn ($q) => $q->whereRaw('total - COALESCE(uang_diterima,0) > 0'))
            ->when($this->search, function ($q) {
                $search = $this->search;
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            })
            ->orderByDesc('transaction_date');

        $items = $query->get();
        $totalPiutang = $items->sum(function ($row) {
            return max(0, $row->total - ($row->uang_diterima ?? 0));
        });

        return view('exports.piutang_report', [
            'items' => $items,
            'totalPiutang' => $totalPiutang,
            'start' => $this->start,
            'end' => $this->end,
            'customerName' => $this->customerName,
            'tempo' => $this->tempo,
            'status' => $this->status,
            'search' => $this->search,
        ]);
    }
}
