<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseProduct extends Model
{
    protected $table = 'warehouse_products';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'variation_id',
        'stock',
        'reserved',
        'min_stock',
        'rack_position',
        'is_active',
    ];

    // === RELASI ===
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
}