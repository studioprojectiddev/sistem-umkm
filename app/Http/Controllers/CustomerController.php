<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CashFlow;
use App\Models\CashflowCategory;

class CustomerController extends Controller
{
    
    public function index()
    {
        // ===============================
        // 🔥 DATA CUSTOMER
        // ===============================
        $customers = DB::table('transactions as t')
            ->leftJoin('accounts as a', 't.account_id', '=', 'a.id')
            ->select(
                't.customer_name',
                't.customer_phone',
                DB::raw('MAX(t.invoice_number) as last_invoice'),
                DB::raw('COUNT(t.id) as total_transaksi'),
                DB::raw('SUM(t.total) as total_transaksi_amount'),
                DB::raw('SUM(t.uang_diterima) as total_bayar'),
                DB::raw('SUM(t.total - t.uang_diterima) as total_unpaid'),
                DB::raw('MAX(t.due_date) as last_due_date'),
                DB::raw("STRING_AGG(DISTINCT a.name, ', ') as metode_pembayaran")
            )
            ->whereNotNull('t.customer_name')
            ->groupBy('t.customer_name', 't.customer_phone')
            ->orderBy('last_due_date', 'asc')
            ->paginate(10);

        // 🔥 MAP SETELAH PAGINATE
        $customers->getCollection()->transform(function ($item) {

            $item->status = $item->total_unpaid > 0 ? 'Belum Lunas' : 'Lunas';

            $item->is_overdue = false;
            if ($item->last_due_date && $item->status == 'Belum Lunas') {
                $item->is_overdue = strtotime($item->last_due_date) < time();
            }

            return $item;
        });

        // ===============================
        // 🔥 DATA ACCOUNT (INI YANG KURANG)
        // ===============================
        $accounts = DB::table('accounts')
            ->orderBy('name', 'asc')
            ->get();

        return view('umkm.customers.data', compact('customers', 'accounts'));
    }

    public function bayar(Request $request)
    {
        DB::beginTransaction();

        try {

            $customer = $request->customer_name;
            $jumlah   = (int) $request->jumlah;
            $accountId = $request->account_id ?? 1; // default kalau mau

            // kategori income
            $category = CashflowCategory::firstOrCreate([
                'name' => 'Pembayaran Piutang',
                'type' => 'income'
            ]);

            $transactions = DB::table('transactions')
                ->where('customer_name', $customer)
                ->where('payment_status', 'unpaid')
                ->orderBy('created_at', 'asc')
                ->get();

            $totalDibayar = 0;

            foreach ($transactions as $t) {

                $sisa = $t->total - $t->uang_diterima;

                if ($jumlah <= 0) break;

                if ($jumlah >= $sisa) {

                    DB::table('transactions')
                        ->where('id', $t->id)
                        ->update([
                            'uang_diterima' => $t->total,
                            'payment_status' => 'paid'
                        ]);

                    $jumlah -= $sisa;
                    $totalDibayar += $sisa;

                } else {

                    DB::table('transactions')
                        ->where('id', $t->id)
                        ->update([
                            'uang_diterima' => $t->uang_diterima + $jumlah
                        ]);

                    $totalDibayar += $jumlah;
                    $jumlah = 0;
                }
            }

            // 🔥 MASUKKAN KE CASHFLOW
            if ($totalDibayar > 0) {

                CashFlow::create([
                    'type' => 'income',
                    'category_id' => $category->id,
                    'amount' => $totalDibayar,
                    'account_id' => $accountId,
                    'transaction_date' => now(),
                    'description' => 'Pembayaran piutang - ' . $customer,
                    'reference_type' => 'piutang',
                    'reference_id' => null,
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
