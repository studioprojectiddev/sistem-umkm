<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\WarehouseProduct;
use App\Models\WarehouseStockTransaction;
use App\Exports\HutangReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class LaporanHutangController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $supplierName = $request->query('supplier_name');
        $tempo = $request->query('tempo');
        $status = $request->query('status');
        $search = $request->query('q');
        $perPage = $request->query('per_page', '10');


        $transactionDateColumn = Schema::hasColumn('warehouse_stock_transactions', 'created_at')
            ? 'created_at'
            : '';
            
        $paidColumn = Schema::hasColumn('warehouse_stock_transactions', 'amount_paid')
            ? 'amount_paid'
            : 'paid';

        $suppliers = WarehouseProduct::whereNotNull('supplier_name')
            ->where('supplier_name', '!=', '')
            ->distinct()
            ->orderBy('supplier_name')
            ->pluck('supplier_name');

        $query = WarehouseStockTransaction::query()
            ->where('action_type', 'add')
            ->when($status === 'lunas', fn ($q) => $q->where('remaining', '=', 0))
            ->when($status === 'belum' || !$status, fn ($q) => $q->where('remaining', '>', 0))
            ->when($start, fn ($q) => $q->whereDate($transactionDateColumn, '>=', $start))
            ->when($end, fn ($q) => $q->whereDate($transactionDateColumn, '<=', $end))
            ->when($supplierName, fn ($q) => $q->where('supplier_name', $supplierName))
            ->when($tempo, function ($q) use ($tempo, $transactionDateColumn) {
                if ($tempo === 'net_30') {
                    $q->whereRaw("(due_date::date - {$transactionDateColumn}::date) <= 30")
                    ->whereRaw("(due_date::date - {$transactionDateColumn}::date) >= 0");
                }
                elseif ($tempo === 'net_60') {
                    $q->whereRaw("(due_date::date - {$transactionDateColumn}::date) > 30")
                    ->whereRaw("(due_date::date - {$transactionDateColumn}::date) <= 60");
                }
                elseif ($tempo === 'net_90') {
                    $q->whereRaw("(due_date::date - {$transactionDateColumn}::date) > 60")
                    ->whereRaw("(due_date::date - {$transactionDateColumn}::date) <= 90");
                }
                elseif ($tempo === 'sdt') {
                    $q->whereDate('due_date', '<', now());
                }
            })
            ->orderByDesc($transactionDateColumn);

        $totalHutang = (clone $query)
            ->reorder()
            ->sum('remaining');

        $totalTransaksi = (clone $query)->reorder()->sum('total');
        $totalTerbayar = (clone $query)->reorder()->sum($paidColumn);

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

        return view('umkm.laporan_hutang', compact(
            'items',
            'suppliers',
            'start',
            'end',
            'supplierName',
            'tempo',
            'status',
            'search',
            'perPage',
            'totalHutang',
            'totalTransaksi',
            'totalTerbayar'
        ));
    }

    public function exportExcel(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $supplierName = $request->query('supplier_name');
        $tempo = $request->query('tempo');
        $status = $request->query('status');
        $search = $request->query('q');

        return Excel::download(
            new HutangReportExport($start, $end, $supplierName, $tempo, $status, $search),
            'laporan-hutang.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $supplierName = $request->query('supplier_name');
        $tempo = $request->query('tempo');
        $status = $request->query('status');

        $transactionDateColumn = Schema::hasColumn('warehouse_stock_transactions', 'created_at')
            ? 'created_at'
            : '';

        $query = WarehouseStockTransaction::query()
            ->where('action_type', 'add')
            ->when($status === 'lunas', fn ($q) => $q->where('remaining', '=', 0))
            ->when($status === 'belum' || !$status, fn ($q) => $q->where('remaining', '>', 0))
            ->when($start, fn ($q) => $q->whereDate($transactionDateColumn, '>=', $start))
            ->when($end, fn ($q) => $q->whereDate($transactionDateColumn, '<=', $end))
            ->when($supplierName, fn ($q) => $q->where('supplier_name', $supplierName))
            ->when($tempo, function ($q) use ($tempo, $transactionDateColumn) {
                if ($tempo === 'net_30') {
                    $q->whereRaw("(due_date::date - {$transactionDateColumn}::date) <= 30")
                    ->whereRaw("(due_date::date - {$transactionDateColumn}::date) >= 0");
                }
                elseif ($tempo === 'net_60') {
                    $q->whereRaw("(due_date::date - {$transactionDateColumn}::date) > 30")
                    ->whereRaw("(due_date::date - {$transactionDateColumn}::date) <= 60");
                }
                elseif ($tempo === 'net_90') {
                    $q->whereRaw("(due_date::date - {$transactionDateColumn}::date) > 60")
                    ->whereRaw("(due_date::date - {$transactionDateColumn}::date) <= 90");
                }
                elseif ($tempo === 'sdt') {
                    $q->whereDate('due_date', '<', now());
                }
            })
            ->orderByDesc($transactionDateColumn);

        $items = $query->get();
        $totalHutang = $items->sum('remaining');

        $pdf = Pdf::loadView('pdf.hutang_report', [
            'items' => $items,
            'totalHutang' => $totalHutang,
            'start' => $start,
            'end' => $end,
            'supplierName' => $supplierName,
            'tempo' => $tempo,
        ]);

        return $pdf->download('laporan-hutang.pdf');
    }
}
