<?php

namespace App\Exports;

use App\Models\CashFlow;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CashflowExport implements FromView
{
    protected $month;
    protected $year;

    public function __construct($month,$year)
    {
        $this->month = $month;
        $this->year  = $year;
    }

    public function view(): View
    {
        $data = CashFlow::with(['category','account'])
            ->whereMonth('transaction_date',$this->month)
            ->whereYear('transaction_date',$this->year)
            ->orderBy('transaction_date')
            ->get();

        return view('exports.cashflow',[
            'cashflows'=>$data,
            'month'=>$this->month,
            'year'=>$this->year
        ]);
    }
}