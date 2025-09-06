<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VariationOption extends Model
{
    use HasFactory;

    protected $table = 'variation_options';
    protected $fillable = ['idpenginput','attribute_id', 'value'];

    // Relasi ke Attribute
    public function attribute()
    {
        return $this->belongsTo(VariationAttribute::class, 'attribute_id');
    }

    // Relasi ke ProductVariation lewat pivot
    public function productVariations()
    {
        return $this->belongsToMany(
            ProductVariation::class,
            'product_variation_options', // pivot table
            'option_id',   // kolom di pivot yang refer ke variation_options
            'variation_id' // kolom di pivot yang refer ke product_variations
        );
    }
}