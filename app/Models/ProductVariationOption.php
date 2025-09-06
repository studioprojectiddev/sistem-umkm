<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariationOption extends Model
{
    use HasFactory;

    protected $table = 'product_variation_options'; // nama tabel
    protected $fillable = ['idpenginput', 'variation_id', 'option_id'];

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    public function option()
    {
        return $this->belongsTo(VariationOption::class, 'option_id');
    }
}