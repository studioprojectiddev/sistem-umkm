<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariation extends Model
{
    use HasFactory;

    protected $table = 'product_variations'; // nama tabel
    protected $fillable = [
        'idpenginput',
        'product_id',
        'name',
        'sku',
        'price',
        'stock',
        'weight',
        'image',
        'is_active'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function options()
    {
        return $this->belongsToMany(
            VariationOption::class,
            'product_variation_options', // konsisten plural
            'variation_id',
            'option_id'
        )->with('attribute');
    }   
    
    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class, 'variation_id');
    }

}