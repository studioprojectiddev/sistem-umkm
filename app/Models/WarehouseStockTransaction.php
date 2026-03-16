<?php

namespace App\Models;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WarehouseStockTransaction extends Model
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
}