<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\WarehouseStockLog;
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

        $suppliers = DB::table('warehouse_stock_logs')
            ->whereNotNull('supplier_name')
            ->where('supplier_name', '!=', '')
            ->distinct()
            ->orderBy('supplier_name')
            ->pluck('supplier_name');

        $query = DB::table('warehouse_stock_logs')
            ->leftJoin('cash_flows', function ($join) {
                $join->on('cash_flows.reference_id', '=', 'warehouse_stock_logs.id')
                    ->whereRaw("cash_flows.reference_type = ?", ['warehouse'])
                    ->whereRaw("cash_flows.type = ?", ['expense']);
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
            ->groupBy([
                'warehouse_stock_logs.id',
                'warehouse_stock_logs.transaction_code',
                'warehouse_stock_logs.created_at',
                'warehouse_stock_logs.supplier_name',
                'warehouse_stock_logs.due_date',
                'warehouse_stock_logs.total'
            ])
            ->when($status === 'lunas', fn ($q) => $q->havingRaw('(warehouse_stock_logs.total - COALESCE(SUM(cash_flows.amount), 0)) = 0'))
            ->when($status === 'belum' || !$status, fn ($q) => $q->havingRaw('(warehouse_stock_logs.total - COALESCE(SUM(cash_flows.amount), 0)) > 0'))
            ->when($start, fn ($q) => $q->whereDate('warehouse_stock_logs.created_at', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('warehouse_stock_logs.created_at', '<=', $end))
            ->when($supplierName, fn ($q) => $q->where('warehouse_stock_logs.supplier_name', $supplierName))
            ->when($tempo, function ($q) use ($tempo) {
                if ($tempo === 'net_30') {
                    $q->whereRaw("(due_date::date - created_at::date) <= 30")
                      ->whereRaw("(due_date::date - created_at::date) >= 0");
                } elseif ($tempo === 'net_60') {
                    $q->whereRaw("(due_date::date - created_at::date) > 30")
                      ->whereRaw("(due_date::date - created_at::date) <= 60");
                } elseif ($tempo === 'net_90') {
                    $q->whereRaw("(due_date::date - created_at::date) > 60")
                      ->whereRaw("(due_date::date - created_at::date) <= 90");
                } elseif ($tempo === 'sdt') {
                    $q->whereDate('due_date', '<', now());
                }
            })
            ->orderByDesc('warehouse_stock_logs.created_at');

        $totalHutang = (clone $query)->sum('sisa_hutang');
        $totalTransaksi = (clone $query)->sum('total');
        $totalTerbayar = (clone $query)->sum('sudah_dibayar');

        if ($perPage === 'all') {
            $collection = $query->get();
            $items = new LengthAwarePaginator(
                $collection,
                $collection->count(),
                $collection->count(),
                1,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
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
            ->when($status === 'lunas', fn ($q) => $q->havingRaw('(warehouse_stock_logs.total - COALESCE(SUM(cash_flows.amount), 0)) = 0'))
            ->when($status === 'belum' || !$status, fn ($q) => $q->havingRaw('(warehouse_stock_logs.total - COALESCE(SUM(cash_flows.amount), 0)) > 0'))
            ->when($start, fn ($q) => $q->whereDate('warehouse_stock_logs.created_at', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('warehouse_stock_logs.created_at', '<=', $end))
            ->when($supplierName, fn ($q) => $q->where('warehouse_stock_logs.supplier_name', $supplierName))
            ->when($tempo, function ($q) use ($tempo) {
                if ($tempo === 'net_30') {
                    $q->whereRaw("(due_date::date - created_at::date) <= 30")
                      ->whereRaw("(due_date::date - created_at::date) >= 0");
                } elseif ($tempo === 'net_60') {
                    $q->whereRaw("(due_date::date - created_at::date) > 30")
                      ->whereRaw("(due_date::date - created_at::date) <= 60");
                } elseif ($tempo === 'net_90') {
                    $q->whereRaw("(due_date::date - created_at::date) > 60")
                      ->whereRaw("(due_date::date - created_at::date) <= 90");
                } elseif ($tempo === 'sdt') {
                    $q->whereDate('due_date', '<', now());
                }
            })
            ->orderByDesc('warehouse_stock_logs.created_at');

        $items = $query->get();
        $totalHutang = $items->sum('sisa_hutang');

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
