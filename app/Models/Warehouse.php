<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'warehouses';

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'phone',
        'pic_name',
        'pic_contact',
        'type',
        'idpenginput',
    ];

    // Relasi: satu gudang punya banyak warehouse_products
    public function warehouseProducts()
    {
        return $this->hasMany(WarehouseProduct::class, 'warehouse_id');
    }

    // Optional: helper untuk total stok gudang (jumlah semua produk)
    public function totalStock()
    {
        return $this->warehouseProducts()->sum(DB::raw('stock'));
    }

    public function products()
    {
        return $this->hasMany(WarehouseProduct::class, 'warehouse_id');
    }

}