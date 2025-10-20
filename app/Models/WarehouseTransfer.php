<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseTransfer extends Model
{
    use HasFactory;

    protected $table = 'warehouse_transfers';

    protected $fillable = [
        'from_warehouse_id',
        'to_warehouse_id',
        'product_id',
        'variation_id',
        'quantity',
        'status',
    ];

    /**
     * ===============================
     * 🔗 RELATIONSHIPS
     * ===============================
     */

    // Gudang asal
    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    // Gudang tujuan
    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    // Produk utama (non-variasi)
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Variasi produk (jika ada)
    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    /**
     * ===============================
     * ⚙️ ACCESSORS & HELPERS
     * ===============================
     */

    // Menampilkan nama produk secara otomatis (dengan variasi jika ada)
    public function getFullProductNameAttribute()
    {
        if ($this->variation) {
            return "{$this->product->name} - {$this->variation->name}";
        }

        return $this->product ? $this->product->name : '-';
    }

    // Warna status untuk tampilan (opsional)
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
            'in_transit' => '<span class="badge bg-info text-dark">In Transit</span>',
            'received' => '<span class="badge bg-success">Received</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}