<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\WarehouseProduct;
use App\Models\WarehouseStockTransaction;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class StokReportExport implements FromView
{
    protected $start;
    protected $end;
    protected $productId;

    public function __construct($start = null, $end = null, $productId = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->productId = $productId;
    }

    public function view(): View
    {
        $query = WarehouseStockTransaction::query()
            ->with(['product', 'variation'])
            ->when($this->start, fn ($q) => $q->whereDate('created_at', '>=', $this->start))
            ->when($this->end, fn ($q) => $q->whereDate('created_at', '<=', $this->end))
            ->when($this->productId, fn ($q) => $q->where('product_id', $this->productId))
            ->orderBy('created_at');

        $items = $query->get();

        $warehouseProductCosts = WarehouseProduct::query()
            ->when($this->productId, fn ($q) => $q->where('product_id', $this->productId))
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

        return view('exports.stok_report', [
            'items' => $items,
            'start' => $this->start,
            'end' => $this->end,
            'productId' => $this->productId,
        ]);
    }
}
