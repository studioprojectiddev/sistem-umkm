<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountOpening extends Model
{
    protected $fillable = [
        'account_id',
        'month',
        'year',
        'opening_balance'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}