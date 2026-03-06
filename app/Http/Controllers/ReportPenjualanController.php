<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\TransactionItem;
use App\Models\Warehouse;
use App\Exports\SalesReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportPenjualanController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $search = $request->query('q');
        $warehouseId = $request->query('warehouse_id');
        $status = $request->query('status');
        $perPage = $request->query('per_page', '10');

        $warehouses = Warehouse::where('type', 'store')
            ->orderBy('name')
            ->get();

        $query = TransactionItem::with(['transaction', 'product', 'variation'])
            ->when($start, fn ($q) => $q->whereHas('transaction', fn ($q) => $q->whereDate('transaction_date', '>=', $start)))
            ->when($end, fn ($q) => $q->whereHas('transaction', fn ($q) => $q->whereDate('transaction_date', '<=', $end)))
            ->when($warehouseId, fn ($q) => $q->whereHas('transaction', fn ($q) => $q->where('warehouse_id', $warehouseId)))
            ->when($status, fn ($q) => $q->whereHas('transaction', fn ($q) => $q->where('status', $status)))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('transaction', function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%");
                })
                ->orWhereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        $total = (clone $query)->sum('subtotal');

        if ($perPage === 'all') {
            $collection = $query->get();
            $items = new LengthAwarePaginator(
                $collection,
                $collection->count(),
                $collection->count(),
                1,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'query' => $request->query(),
                ]
            );
        } else {
            $perPageNumber = (int) $perPage;
            $perPageNumber = $perPageNumber > 0 ? $perPageNumber : 10;
            $items = $query->paginate($perPageNumber)->withQueryString();
        }

        return view('umkm.report_penjualan', compact(
            'items',
            'total',
            'start',
            'end',
            'search',
            'warehouses',
            'warehouseId',
            'status',
            'perPage'
        ));
    }

    public function exportExcel(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $search = $request->query('q');
        $warehouseId = $request->query('warehouse_id');
        $status = $request->query('status');

        return Excel::download(
            new SalesReportExport($start, $end, $search, $warehouseId, $status),
            'laporan-penjualan.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $search = $request->query('q');
        $warehouseId = $request->query('warehouse_id');
        $status = $request->query('status');

        $query = TransactionItem::with(['transaction', 'product', 'variation'])
            ->when($start, fn ($q) => $q->whereHas('transaction', fn ($q) => $q->whereDate('transaction_date', '>=', $start)))
            ->when($end, fn ($q) => $q->whereHas('transaction', fn ($q) => $q->whereDate('transaction_date', '<=', $end)))
            ->when($warehouseId, fn ($q) => $q->whereHas('transaction', fn ($q) => $q->where('warehouse_id', $warehouseId)))
            ->when($status, fn ($q) => $q->whereHas('transaction', fn ($q) => $q->where('status', $status)))
            ->when($search, function ($q) use ($search) {
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

        $pdf = Pdf::loadView('pdf.report_penjualan', [
            'items' => $items,
            'total' => $total,
            'start' => $start,
            'end' => $end,
            'warehouseId' => $warehouseId,
            'status' => $status,
        ]);

        return $pdf->download('laporan-penjualan.pdf');
    }
}
