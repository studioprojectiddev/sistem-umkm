<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_type',        // product / variation
        'item_id',          // id produk atau variasi
        'transaction_type', // in / out / adjust
        'quantity',
        'supplier',
        'note',
        'user_id'
    ];

    /**
     * Polymorphic relation ke Product atau ProductVariation
     * - item_type = App\Models\Product / App\Models\ProductVariation
     */
    public function item()
    {
        return $this->morphTo(__FUNCTION__, 'item_type', 'item_id');
    }

    /**
     * User yang melakukan transaksi stok
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor untuk memberi label transaksi
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->transaction_type) {
            'in'     => 'Tambah Stok',
            'out'    => 'Pengurangan Stok',
            'adjust' => 'Penyesuaian',
            default  => ucfirst($this->transaction_type)
        };
    }
}