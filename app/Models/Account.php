<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['name','type','type_account','initial_balance'];

    public function cashflows()
    {
        return $this->hasMany(CashFlow::class);
    }
}
