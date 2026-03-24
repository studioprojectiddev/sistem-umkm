<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

use Illuminate\Http\Request;

class InsightController extends Controller
{
    public function penjualan(Request $request)
    {
        // ================= FILTER TANGGAL =================
        $start = $request->start_date ?? now()->startOfMonth();
        $end   = $request->end_date ?? now()->endOfMonth();

        // ================= QUERY DASAR =================
        $baseQuery = DB::table('transactions')
            ->where('transaction_type', 'sale')
            ->whereBetween('created_at', [$start, $end]);

        // ================= TOTAL =================
        $totalPenjualan = (clone $baseQuery)->sum('total');

        $totalTransaksi = (clone $baseQuery)->count();

        $totalPelanggan = (clone $baseQuery)
            ->whereNotNull('customer_name')
            ->distinct('customer_name')
            ->count('customer_name');

        $totalPiutang = (clone $baseQuery)
            ->sum(DB::raw('total - uang_diterima'));

        // ================= TOP CUSTOMER =================
        $topCustomers = (clone $baseQuery)
            ->select('customer_name', DB::raw('SUM(total - uang_diterima) as total_belanja'))
            ->whereNotNull('customer_name')
            ->groupBy('customer_name')
            ->orderByDesc('total_belanja')
            ->limit(5)
            ->get();

        // ================= BULAN SEKARANG =================
        $nowStart = now()->startOfMonth();
        $nowEnd   = now()->endOfMonth();

        // ================= BULAN SEBELUM =================
        $lastStart = now()->subMonth()->startOfMonth();
        $lastEnd   = now()->subMonth()->endOfMonth();

        // ================= FUNCTION GROWTH =================
        $growth = function ($now, $last) {
            if ($last == 0) return $now > 0 ? 100 : 0;
            return (($now - $last) / $last) * 100;
        };

        // ================= PENJUALAN =================
        $penjualanNow = DB::table('transactions')
            ->where('transaction_type', 'sale')
            ->whereBetween('created_at', [$nowStart, $nowEnd])
            ->sum('total');

        $penjualanLast = DB::table('transactions')
            ->where('transaction_type', 'sale')
            ->whereBetween('created_at', [$lastStart, $lastEnd])
            ->sum('total');

        $growthPenjualan = $growth($penjualanNow, $penjualanLast);

        // ================= TRANSAKSI =================
        $transaksiNow = DB::table('transactions')
            ->where('transaction_type', 'sale')
            ->whereBetween('created_at', [$nowStart, $nowEnd])
            ->count();

        $transaksiLast = DB::table('transactions')
            ->where('transaction_type', 'sale')
            ->whereBetween('created_at', [$lastStart, $lastEnd])
            ->count();

        $growthTransaksi = $growth($transaksiNow, $transaksiLast);

        // ================= PELANGGAN =================
        $pelangganNow = DB::table('transactions')
            ->whereBetween('created_at', [$nowStart, $nowEnd])
            ->distinct('customer_name')
            ->count('customer_name');

        $pelangganLast = DB::table('transactions')
            ->whereBetween('created_at', [$lastStart, $lastEnd])
            ->distinct('customer_name')
            ->count('customer_name');

        $growthPelanggan = $growth($pelangganNow, $pelangganLast);

        // ================= PIUTANG =================
        $piutangNow = DB::table('transactions')
            ->whereBetween('created_at', [$nowStart, $nowEnd])
            ->sum(DB::raw('total - uang_diterima'));

        $piutangLast = DB::table('transactions')
            ->whereBetween('created_at', [$lastStart, $lastEnd])
            ->sum(DB::raw('total - uang_diterima'));

        $growthPiutang = $growth($piutangNow, $piutangLast);

        // ================= RETURN =================
        return view('umkm.insights.penjualan', compact(
            'totalPenjualan',
            'totalTransaksi',
            'totalPelanggan',
            'totalPiutang',
            'topCustomers',
            'growthPenjualan',
            'growthTransaksi',
            'growthPelanggan',
            'growthPiutang'
        ));
    }
}
