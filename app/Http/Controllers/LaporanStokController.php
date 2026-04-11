<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Product;
use App\Models\WarehouseProduct;
use App\Models\WarehouseStockLog;
use App\Exports\StokReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class LaporanStokController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $productId = $request->query('product_id');
        $perPage = $request->query('per_page', '10');

        $products = Product::orderBy('name')->pluck('name', 'id');

        $items = WarehouseStockLog::getStockReportItems($start, $end, $productId);

        if ($perPage === 'all') {
            $collection = $items;
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
            $currentPage = $request->query('page', 1);
            $items = new LengthAwarePaginator(
                $items->forPage($currentPage, $perPageNumber),
                $items->count(),
                $perPageNumber,
                $currentPage,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );
            $items->appends($request->query());
        }

        return view('umkm.laporan_stok', compact(
            'items',
            'products',
            'start',
            'end',
            'productId',
            'perPage'
        ));
    }

    public function exportExcel(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $productId = $request->query('product_id');
        $perPage = $request->query('per_page');

        return Excel::download(
            new StokReportExport($start, $end, $productId),
            'laporan-stok.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $productId = $request->query('product_id');

        $items = WarehouseStockLog::getStockReportItems($start, $end, $productId);

        $pdf = Pdf::loadView('pdf.stok_report', [
            'items' => $items,
            'start' => $start,
            'end' => $end,
            'productId' => $productId,
        ]);

        return $pdf->download('laporan-stok.pdf');
    }
}
