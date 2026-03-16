<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Product;
use App\Models\WarehouseProduct;
use App\Models\WarehouseStockTransaction;
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

        $query = WarehouseStockTransaction::query()
            ->with(['product', 'variation'])
            ->when($start, fn ($q) => $q->whereDate('created_at', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('created_at', '<=', $end))
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->orderBy('created_at');

        $items = $query->get();

        $warehouseProductCosts = WarehouseProduct::query()
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->get()
            ->keyBy(fn ($w) => $w->warehouse_id . '_' . $w->product_id . '_' . ($w->variation_id ?? 0));

        // Hitung saldo per produk/variasi
        $balances = [];
        $items = $items->map(function ($item) use (&$balances, $warehouseProductCosts) {
            $key = $item->warehouse_id . '_' . $item->product_id . '_' . ($item->variation_id ?? 0);
            $stok_awal = $balances[$key] ?? 0;
            $in = $item->action_type === 'add' ? $item->quantity : 0;
            $out = $item->action_type === 'reduce' ? $item->quantity : 0;
            $saldo = $stok_awal + $in - $out;

            $averageCost = optional($warehouseProductCosts->get($key))->avg_cost;
            $hpp = $item->price ?? $averageCost ?? optional($item->product)->cost_price ?? 0;

            $hargaJual = optional($item->variation)->price ?? optional($item->product)->price ?? 0;
            $nilaiStok = $saldo * $hpp;
            $potensiLaba = ($hargaJual - $hpp) * $saldo;

            $balances[$key] = $saldo;
            $item->stok_awal = $stok_awal;
            $item->stok_masuk = $in;
            $item->stok_keluar = $out;
            $item->saldo = $saldo;
            $item->harga_beli = $item->price ?? $averageCost ?? 0;
            $item->hpp = $hpp;
            $item->harga_jual = $hargaJual;
            $item->nilai_stok = $nilaiStok;
            $item->potensi_laba = $potensiLaba;

            return $item;
        });

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

        $query = WarehouseStockTransaction::query()
            ->with(['product', 'variation'])
            ->when($start, fn ($q) => $q->whereDate('created_at', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('created_at', '<=', $end))
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->orderBy('created_at');

        $items = $query->get();

        $warehouseProductCosts = WarehouseProduct::query()
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->get()
            ->keyBy(fn ($w) => $w->warehouse_id . '_' . $w->product_id . '_' . ($w->variation_id ?? 0));

        $balances = [];
        $items = $items->map(function ($item) use (&$balances, $warehouseProductCosts) {
            $key = $item->warehouse_id . '_' . $item->product_id . '_' . ($item->variation_id ?? 0);
            $stok_awal = $balances[$key] ?? 0;
            $in = $item->action_type === 'add' ? $item->quantity : 0;
            $out = $item->action_type === 'reduce' ? $item->quantity : 0;
            $saldo = $stok_awal + $in - $out;

            $averageCost = optional($warehouseProductCosts->get($key))->avg_cost;
            $hpp = $item->price ?? $averageCost ?? optional($item->product)->cost_price ?? 0;
            $hargaJual = optional($item->variation)->price ?? optional($item->product)->price ?? 0;
            $nilaiStok = $saldo * $hpp;
            $potensiLaba = ($hargaJual - $hpp) * $saldo;

            $balances[$key] = $saldo;
            $item->stok_awal = $stok_awal;
            $item->stok_masuk = $in;
            $item->stok_keluar = $out;
            $item->saldo = $saldo;
            $item->harga_beli = $item->price ?? $averageCost ?? 0;
            $item->hpp = $hpp;
            $item->harga_jual = $hargaJual;
            $item->nilai_stok = $nilaiStok;
            $item->potensi_laba = $potensiLaba;

            return $item;
        });

        $pdf = Pdf::loadView('pdf.stok_report', [
            'items' => $items,
            'start' => $start,
            'end' => $end,
            'productId' => $productId,
        ]);

        return $pdf->download('laporan-stok.pdf');
    }
}
