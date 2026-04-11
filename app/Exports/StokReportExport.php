<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\WarehouseProduct;
use App\Models\WarehouseStockLog;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class StokReportExport implements FromView
{
    protected $start;
    protected $end;
    protected $productId;

    public function __construct($start = null, $end = null, $productId = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->productId = $productId;
    }

    public function view(): View
    {
        $items = WarehouseStockLog::getStockReportItems($this->start, $this->end, $this->productId);

        return view('exports.stok_report', [
            'items' => $items,
            'start' => $this->start,
            'end' => $this->end,
            'productId' => $this->productId,
        ]);
    }
}
