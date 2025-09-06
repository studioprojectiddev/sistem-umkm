<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VariationAttribute extends Model
{
    use HasFactory;

    protected $table = 'variation_attributes'; // nama tabel
    protected $fillable = ['idpenginput', 'name'];

    public function options()
    {
        return $this->hasMany(VariationOption::class, 'attribute_id');
    }
}