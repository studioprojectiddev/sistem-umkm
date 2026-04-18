<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 🔥 TOTAL PENJUALAN HARI INI
        $totalPenjualan = DB::table('transactions')
            ->where('transaction_type', 'sale')
            ->whereDate('created_at', $today)
            ->sum('total');

        // 🔥 TOTAL TRANSAKSI
        $totalTransaksi = DB::table('transactions')
            ->where('transaction_type', 'sale')
            ->whereDate('created_at', $today)
            ->count();

        // 🔥 TOTAL PELANGGAN
        $totalPelanggan = DB::table('transactions')
            ->whereNotNull('customer_name')
            ->distinct('customer_name')
            ->count('customer_name');

        // 🔥 TOTAL PIUTANG
        $totalPiutang = DB::table('transactions')
            ->where('payment_status', 'unpaid')
            ->sum('total');

        // 🔥 TOP CUSTOMER
        $topCustomers = DB::table('transactions')
            ->select('customer_name', DB::raw('SUM(total) as total'))
            ->whereNotNull('customer_name')
            ->groupBy('customer_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $start = Carbon::today()->subDays(6);

        $dataChart = DB::table('transactions')
            ->select(
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(total) as total')
            )
            ->where('transaction_type', 'sale')
            ->whereDate('created_at', '>=', $start)
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // 🔥 FORMAT BIAR RAPI
        $labels = [];
        $values = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');

            $found = $dataChart->firstWhere('tanggal', $date);

            $labels[] = date('d M', strtotime($date));
            $values[] = $found->total ?? 0;
        }

        $topProducts = DB::table('transaction_items as ti')
            ->join('products as p', 'ti.product_id', '=', 'p.id')
            ->select(
                'p.name',
                DB::raw('SUM(ti.quantity) as total_qty')
            )
            ->groupBy('p.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $productLabels = $topProducts->pluck('name');
        $productValues = $topProducts->pluck('total_qty');

        // ===============================
        // 🔥 INSIGHT PINTAR
        // ===============================
        $insights = [];

        // 1. PIUTANG
        if($totalPiutang > 0){
            $insights[] = [
                'type' => 'danger',
                'text' => 'Ada piutang Rp ' . number_format($totalPiutang) . ', segera follow up pelanggan'
            ];
        }

        // 2. PRODUK TERLARIS
        if($topProducts->count() > 0){
            $bestProduct = $topProducts->first();

            $insights[] = [
                'type' => 'success',
                'text' => 'Produk terlaris: ' . $bestProduct->name . ', pertimbangkan tambah stok'
            ];
        }

        // 3. PRODUK SEPI (SLOW MOVING)
        $slowProduct = DB::table('transaction_items as ti')
            ->join('products as p', 'ti.product_id','=','p.id')
            ->select('p.name', DB::raw('SUM(ti.quantity) as total_qty'))
            ->groupBy('p.name')
            ->orderBy('total_qty')
            ->first();

        if($slowProduct && $slowProduct->total_qty < 3){
            $insights[] = [
                'type' => 'warning',
                'text' => 'Produk "' . $slowProduct->name . '" kurang laku, coba promo'
            ];
        }

        // 4. TREND PENJUALAN
        if(count($values) >= 2){
            $last = end($values);
            $prev = $values[count($values)-2];

            if($last > $prev){
                $insights[] = [
                    'type' => 'success',
                    'text' => 'Penjualan meningkat dibanding kemarin 🚀'
                ];
            } elseif($last < $prev){
                $insights[] = [
                    'type' => 'danger',
                    'text' => 'Penjualan menurun dibanding kemarin ⚠️'
                ];
            }
        }

        // 🔥 DATA CUSTOMER UTANG
        $customersUtang = DB::table('transactions')
            ->select(
                'customer_name',
                'customer_phone',
                DB::raw('SUM(total - uang_diterima) as sisa_utang')
            )
            ->where('payment_status','unpaid')
            ->whereNotNull('customer_name')
            ->groupBy('customer_name','customer_phone')
            ->get();

        return view('umkm.dashboard', compact(
            'totalPenjualan',
            'totalTransaksi',
            'totalPelanggan',
            'totalPiutang',
            'topCustomers',
            'labels',
            'values',
            'productLabels',
            'productValues',
            'insights',
            'customersUtang'
        ));
    }
}
