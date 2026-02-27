<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashFlow extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'category_id',
        'account_id',
        'amount',
        'transaction_date',
        'description',
        'reference_type',
        'reference_id',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    // 🔹 Relasi kategori
    public function category()
    {
        return $this->belongsTo(CashflowCategory::class);
    }

    // 🔹 Relasi rekening
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // 🔹 Relasi user pembuat
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}