<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accounting extends Model
{
    protected $table = 'accountings';

    public $timestamps = false;

    protected $fillable = [
        'journal_number',
        'reference_type',
        'reference_id',
        'total_debit',
        'total_credit',
        'status_accounting',
        'is_reversal',
        'reversal_of',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];

    protected $casts = [
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'is_reversal' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(AccountingDetail::class, 'accounting_id');
    }

    public function reversal()
    {
        return $this->belongsTo(self::class, 'reversal_of');
    }

    public function reversals()
    {
        return $this->hasMany(self::class, 'reversal_of');
    }
}
