<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'idpenginput',
        'name',
        'slug',
        'code',
        'parent_id',
        'description',
        'icon',
        'banner',
        'is_active',
        'sort_order',
        'meta_title',
        'meta_keywords',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // relasi ke produk
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // parent category (nullable)
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    // children categories
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
