<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        // Identitas produk
        'idpenginput',
        'name',
        'slug',
        'sku',
        'barcode',
        'description',
    
        // Relasi
        'category_id',
        'user_id',
    
        // Harga & stok
        'price',
        'discount_price',
        'cost_price',
        'stock',
        'min_stock',
        'unit',
    
        // Jenis produk
        'product_type', // 'goods' atau 'service'
    
        // Batch / Expired
        'expiry_date',
        'batch_number',
    
        // Media
        'thumbnail',
        'images',
    
        // Variasi / atribut
        'attributes',
    
        // Status
        'is_active',
        'is_featured',
    
        // Promosi
        'is_promo',
        'promo_price',
        'promo_start',
        'promo_end',
    
        // SEO
        'meta_title',
        'meta_keywords',
        'meta_description',
    
        // AI
        'ai_insights',
    ];
    

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    // Relasi ke Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi ke UMKM (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class, 'product_id');
    }

    public function getFinalPriceAttribute()
    {
        $today = now();

        if ($this->is_promo 
            && $this->promo_price 
            && $this->promo_start 
            && $this->promo_end 
            && $this->promo_start <= $today 
            && $this->promo_end >= $today) {
            return $this->promo_price;
        }

        return $this->price;
    }

    // Auto generate slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function salesSummary()
    {
        return $this->hasMany(ProductSalesSummary::class, 'product_id');
    }

}