<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingDetail extends Model
{
    protected $table = 'accounting_details';

    public $timestamps = false;

    protected $fillable = [
        'accounting_id',
        'coa_id',
        'debit',
        'credit',
        'description',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function accounting()
    {
        return $this->belongsTo(Accounting::class, 'accounting_id');
    }

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }
}
