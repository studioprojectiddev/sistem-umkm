<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSalesSummary extends Model
{
    use HasFactory;

    protected $table = 'product_sales_summary';
    protected $fillable = [
        'product_id',
        'variation_id',
        'idpenginput',
        'total_qty',
        'total_sales',
        'date'
    ];

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
}