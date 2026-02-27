<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashflowClosing extends Model
{
    protected $fillable = [
        'month',
        'year',
        'closed_at',
        'closed_by'
    ];
}
