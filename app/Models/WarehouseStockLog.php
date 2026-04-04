<?php

namespace App\Models;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\WarehouseProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class WarehouseStockLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'variation_id',
        'action_type',
        'quantity',
        'min_stock',
        'rack_position',
        'price',
        'total',
        'paid',
        'remaining',
        'supplier_name',
        'due_date',
        'idpenginput',
        'transaction_code',
        'account_id',
        'transaction_date',
        'payment_status',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    public static function getStockReportItems($start = null, $end = null, $productId = null)
    {
        $query = self::query()
            ->with(['product', 'variation', 'warehouse'])
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($start, fn($q) => $q->whereDate('created_at', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('created_at', '<=', $end))
            ->orderBy('warehouse_id')
            ->orderBy('product_id')
            ->orderBy('variation_id')
            ->orderBy('created_at');

        $logs = $query->get();

        if ($logs->isEmpty()) {
            return collect();
        }

        $openingBalances = collect();
        if ($start) {
            $openingBalances = self::query()
                ->when($productId, fn($q) => $q->where('product_id', $productId))
                ->whereDate('created_at', '<', $start)
                ->selectRaw(
                    "warehouse_id, product_id, COALESCE(variation_id, 0) as variation_id, " .
                    "SUM(CASE WHEN action_type IN ('add','transfer_in') THEN quantity ELSE 0 END) as masuk, " .
                    "SUM(CASE WHEN action_type IN ('reduce','transfer_out') THEN quantity ELSE 0 END) as keluar"
                )
                ->groupBy('warehouse_id', 'product_id', 'variation_id')
                ->get()
                ->mapWithKeys(fn($row) => [
                    $row->warehouse_id . '_' . $row->product_id . '_' . $row->variation_id =>
                        ((int)$row->masuk - (int)$row->keluar),
                ]);
        }

        $warehouseProductCosts = WarehouseProduct::query()
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->orderByDesc('id')
            ->get()
            ->keyBy(fn($w) => $w->warehouse_id . '_' . $w->product_id . '_' . ($w->variation_id ?? 0));

        $items = $logs->groupBy(fn($item) => $item->warehouse_id . '_' . $item->product_id . '_' . ($item->variation_id ?? 0))
            ->map(function ($group, $key) use ($openingBalances, $warehouseProductCosts) {
                $first = $group->first();
                $stok_awal = $openingBalances[$key] ?? 0;
                $stok_masuk = $group->whereIn('action_type', ['add', 'transfer_in'])->sum('quantity');
                $stok_keluar = $group->whereIn('action_type', ['reduce', 'transfer_out'])->sum('quantity');
                $stok_akhir = $stok_awal + $stok_masuk - $stok_keluar;

                $warehouseProduct = $warehouseProductCosts[$key] ?? null;
                $avgCost = optional($warehouseProduct)->avg_cost;
                $harga_beli = $avgCost ?? ($group->last()->price ?? optional($first->product)->cost_price ?? 0);
                $hpp = $avgCost ?? ($group->last()->price ?? optional($first->product)->cost_price ?? 0);
                $harga_jual = optional($first->variation)->price ?? optional($first->product)->price ?? 0;
                $nilai_stok = $stok_akhir * $hpp;
                $potensi_laba = ($harga_jual - $hpp) * $stok_akhir;

                return (object) [
                    'created_at' => $first->created_at,
                    'warehouse_id' => $first->warehouse_id,
                    'warehouse_name' => optional($first->warehouse)->name,
                    'product_id' => $first->product_id,
                    'variation_id' => $first->variation_id,
                    'product' => $first->product,
                    'variation' => $first->variation,
                    'stok_awal' => $stok_awal,
                    'stok_masuk' => $stok_masuk,
                    'stok_keluar' => $stok_keluar,
                    'stok_akhir' => $stok_akhir,
                    'harga_beli' => $harga_beli,
                    'hpp' => $hpp,
                    'harga_jual' => $harga_jual,
                    'nilai_stok' => $nilai_stok,
                    'potensi_laba' => $potensi_laba,
                    'min_stock' => optional($warehouseProduct)->min_stock ?? 0,
                ];
            })->values();

        return $items;
    }
}