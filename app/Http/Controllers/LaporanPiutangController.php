<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Exports\PiutangReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class LaporanPiutangController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $customerName = $request->query('customer_name');
        $tempo = $request->query('tempo');
        $status = $request->query('status');
        $search = $request->query('q');
        $perPage = $request->query('per_page', '10');

        $customers = Transaction::whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->distinct()
            ->orderBy('customer_name')
            ->pluck('customer_name');

        $query = Transaction::query()
            ->where('transaction_type', 'sale')
            ->when($start, fn ($q) => $q->whereDate('transaction_date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('transaction_date', '<=', $end))
            ->when($customerName, fn ($q) => $q->where('customer_name', $customerName))
            ->when($tempo, function ($q) use ($tempo) {
                if ($tempo === 'net_15') {
                    $q->whereRaw('(due_date::date - CURRENT_DATE) <= ?', [15]);
                } elseif ($tempo === 'net_30') {
                    $q->whereRaw('(due_date::date - CURRENT_DATE) BETWEEN ? AND ?', [16, 30]);
                } elseif ($tempo === 'net_60') {
                    $q->whereRaw('(due_date::date - CURRENT_DATE) BETWEEN ? AND ?', [31, 60]);
                } elseif ($tempo === 'net_90') {
                    $q->whereRaw('(due_date::date - CURRENT_DATE) BETWEEN ? AND ?', [61, 90]);
                } elseif ($tempo === 'sdt') {
                    $q->whereRaw('due_date::date < CURRENT_DATE');
                }
            })
            ->when($status === 'belum', fn ($q) => $q->whereRaw('total - COALESCE(uang_diterima,0) > 0'))
            ->when($status === 'lunas', fn ($q) => $q->whereRaw('total - COALESCE(uang_diterima,0) <= 0'))
            ->when(!$status, fn ($q) => $q->whereRaw('total - COALESCE(uang_diterima,0) > 0'))
            ->when($search, function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            })
            ->orderByDesc('transaction_date');

        $totalPiutang = (clone $query)
            ->reorder()
            ->sum(DB::raw('total - COALESCE(uang_diterima,0)'));

        $totalPenjualan = (clone $query)->reorder()->sum('total');
        $totalTerbayar = (clone $query)->reorder()->sum('uang_diterima');

        if ($perPage === 'all') {
            $collection = $query->get();
            $items = new LengthAwarePaginator(
                $collection,
                $collection->count(),
                $collection->count(),
                1,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                ]
            );
            $items->appends($request->query());
        } else {
            $perPageNumber = (int) $perPage;
            $perPageNumber = $perPageNumber > 0 ? $perPageNumber : 10;
            $items = $query->paginate($perPageNumber)->appends($request->query());
        }

        return view('umkm.laporan_piutang', compact(
            'items',
            'customers',
            'start',
            'end',
            'customerName',
            'tempo',
            'status',
            'search',
            'perPage',
            'totalPiutang',
            'totalPenjualan',
            'totalTerbayar'
        ));
    }

    public function exportExcel(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $customerName = $request->query('customer_name');
        $tempo = $request->query('tempo');
        $status = $request->query('status');
        $search = $request->query('q');

        return Excel::download(
            new PiutangReportExport($start, $end, $customerName, $tempo, $status, $search),
            'laporan-piutang.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $customerName = $request->query('customer_name');
        $tempo = $request->query('tempo');
        $status = $request->query('status');
        $search = $request->query('q');

        $query = Transaction::query()
            ->where('transaction_type', 'sale')
            ->when($start, fn ($q) => $q->whereDate('transaction_date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('transaction_date', '<=', $end))
            ->when($customerName, fn ($q) => $q->where('customer_name', $customerName))
            ->when($tempo, function ($q) use ($tempo) {
                if ($tempo === 'net_15') {
                    $q->whereRaw('(due_date::date - CURRENT_DATE) <= ?', [15]);
                } elseif ($tempo === 'net_30') {
                    $q->whereRaw('(due_date::date - CURRENT_DATE) BETWEEN ? AND ?', [16, 30]);
                } elseif ($tempo === 'net_60') {
                    $q->whereRaw('(due_date::date - CURRENT_DATE) BETWEEN ? AND ?', [31, 60]);
                } elseif ($tempo === 'net_90') {
                    $q->whereRaw('(due_date::date - CURRENT_DATE) BETWEEN ? AND ?', [61, 90]);
                } elseif ($tempo === 'sdt') {
                    $q->whereRaw('due_date::date < CURRENT_DATE');
                }
            })
            ->when($status === 'belum', fn ($q) => $q->whereRaw('total - COALESCE(uang_diterima,0) > 0'))
            ->when($status === 'lunas', fn ($q) => $q->whereRaw('total - COALESCE(uang_diterima,0) <= 0'))
            ->when(!$status, fn ($q) => $q->whereRaw('total - COALESCE(uang_diterima,0) > 0'))
            ->when($search, function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            })
            ->orderByDesc('transaction_date');

        $items = $query->get();
        $totalPiutang = $items->sum(function ($row) {
            return max(0, $row->total - ($row->uang_diterima ?? 0));
        });

        $pdf = Pdf::loadView('pdf.piutang_report', [
            'items' => $items,
            'totalPiutang' => $totalPiutang,
            'start' => $start,
            'end' => $end,
            'customerName' => $customerName,
            'tempo' => $tempo,
            'status' => $status,
            'search' => $search,
        ]);

        return $pdf->download('laporan-piutang.pdf');
    }
}
