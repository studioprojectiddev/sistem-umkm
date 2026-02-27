<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'transaction_type',
        'idpenginput',
        'user_id',
        'subtotal',
        'discount',
        'tax',
        'shipping_cost',
        'total',
        'payment_status',
        'payment_method',
        'uang_diterima',
        'customer_name',
        'due_date',
        'kembalian',
        'status',
        'account_id'
    ];

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}