<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashFlow extends Model
{
    use SoftDeletes;

    public const STATUS_WAITING = 'waiting_check';
    public const STATUS_CHECKED = 'checked';
    public const STATUS_POSTING = 'posting';
    public const STATUS_VOID = 'void';

    protected $fillable = [
        'type',
        'category_id',
        'account_id',
        'amount',
        'transaction_date',
        'status_accounting',
        'checked_by',
        'checked_at',
        'posted_by',
        'posted_at',
        'void_by',
        'void_at',
        'void_reason',
        'description',
        'reference_type',
        'reference_id',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'checked_at' => 'datetime',
        'posted_at' => 'datetime',
        'void_at' => 'datetime',
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