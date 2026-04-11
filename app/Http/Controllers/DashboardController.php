<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->query('year', Carbon::now()->year);
        $month = $request->query('month');
        $hasMonth = $month && is_numeric($month) && $month >= 1 && $month <= 12;

        if ($hasMonth) {
            $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
            $periodEnd = $periodStart->copy()->endOfMonth();
        } else {
            $periodStart = Carbon::create($year, 1, 1)->startOfYear();
            $periodEnd = Carbon::create($year, 12, 31)->endOfYear();
        }

        $monthNames = collect(range(1, 12))->mapWithKeys(function ($number) {
            return [$number => Carbon::createFromDate(2000, $number, 1)->translatedFormat('M')];
        })->toArray();

        $cashBankInitial = DB::table('accounts')
            ->whereIn('type', ['cash', 'bank'])
            ->sum('initial_balance');

        $cashBankFlow = DB::table('cash_flows')
            ->join('accounts', 'cash_flows.account_id', '=', 'accounts.id')
            ->whereIn('accounts.type', ['cash', 'bank'])
            ->selectRaw("SUM(CASE WHEN cash_flows.type = 'income' THEN cash_flows.amount WHEN cash_flows.type = 'expense' THEN -cash_flows.amount ELSE 0 END) AS balance")
            ->value('balance');

        $cashBankBalance = $cashBankInitial + ($cashBankFlow ?: 0);

        $incomeTotal = (float) DB::table('cash_flows')
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->where('type', 'income')
            ->sum('amount');

        $expenseTotal = (float) DB::table('cash_flows')
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->where('type', 'expense')
            ->sum('amount');

        $profitNet = $incomeTotal - $expenseTotal;

        $cashIn = $incomeTotal;
        $cashOut = $expenseTotal;

        $previousBalance = (float) DB::table('cash_flows')
            ->where('transaction_date', '<', $periodStart)
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount WHEN type = 'expense' THEN -amount ELSE 0 END) AS balance")
            ->value('balance');

        $previousBalance = $previousBalance ?: 0;
        $endingBalance = $previousBalance + ($cashIn - $cashOut);

        $receivableTotal = (float) DB::table('transactions')
            ->where('transaction_type', 'sale')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->selectRaw("SUM(COALESCE(total, 0) - COALESCE(uang_diterima, 0) + COALESCE(kembalian, 0)) AS amount")
            ->value('amount');

        $receivables = DB::table('transactions')
            ->where('transaction_type', 'sale')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->selectRaw("id, invoice_number, COALESCE(customer_name, '') AS customer_name, transaction_date, total, COALESCE(uang_diterima, 0) AS uang_diterima, COALESCE(kembalian, 0) AS kembalian, (COALESCE(total, 0) - COALESCE(uang_diterima, 0) + COALESCE(kembalian, 0)) AS receivable")
            ->whereRaw("(COALESCE(total, 0) - COALESCE(uang_diterima, 0) + COALESCE(kembalian, 0)) > 0")
            ->orderByDesc('receivable')
            ->limit(5)
            ->get();

        $payableTotal = (float) DB::table('warehouse_stock_logs')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->selectRaw("SUM(COALESCE(remaining, COALESCE(total, 0) - COALESCE(paid, 0), 0)) AS amount")
            ->value('amount');

        $payables = DB::table('warehouse_stock_logs')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->selectRaw("id, supplier_name, transaction_date, note, COALESCE(total, 0) AS total, COALESCE(paid, 0) AS paid, COALESCE(remaining, COALESCE(total, 0) - COALESCE(paid, 0), 0) AS payable")
            ->whereRaw("COALESCE(remaining, COALESCE(total, 0) - COALESCE(paid, 0), 0) > 0")
            ->orderByDesc('payable')
            ->limit(5)
            ->get();

        $recentCashFlows = DB::table('cash_flows')
            ->select('id', 'transaction_date', 'description', 'reference', 'type', 'amount')
            ->orderByDesc('transaction_date')
            ->limit(7)
            ->get();

        $expenseCategories = DB::table('cash_flows')
            ->selectRaw("CASE WHEN reference_type ILIKE '%warehouse%' THEN 'Pembelian' WHEN reference_type ILIKE '%cashflow%' THEN 'Biaya operasional' ELSE 'Lainnya' END AS category")
            ->selectRaw('SUM(amount) AS total')
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->where('type', 'expense')
            ->groupByRaw("CASE WHEN reference_type ILIKE '%warehouse%' THEN 'Pembelian' WHEN reference_type ILIKE '%cashflow%' THEN 'Biaya operasional' ELSE 'Lainnya' END")
            ->orderByDesc('total')
            ->get();

        $monthlyData = DB::table('cash_flows')
            ->selectRaw("DATE_PART('month', transaction_date)::int AS month")
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expense")
            ->whereRaw("DATE_PART('year', transaction_date)::int = ?", [$year])
            ->groupByRaw('month')
            ->orderByRaw('month')
            ->get()
            ->keyBy('month');

        $incomeByMonth = [];
        $expenseByMonth = [];
        $monthLabels = [];

        foreach ($monthNames as $number => $label) {
            $monthLabels[] = $label;
            $incomeByMonth[] = isset($monthlyData[$number]) ? (float) $monthlyData[$number]->income : 0;
            $expenseByMonth[] = isset($monthlyData[$number]) ? (float) $monthlyData[$number]->expense : 0;
        }

        $expenseCategoriesChart = $expenseCategories->map(function ($item) use ($expenseTotal) {
            return [
                'category' => $item->category,
                'total' => (float) $item->total,
                'percent' => $expenseTotal > 0 ? round(($item->total / $expenseTotal) * 100, 1) : 0,
            ];
        });

        return view('dashboard', compact(
            'year',
            'month',
            'hasMonth',
            'cashBankBalance',
            'incomeTotal',
            'expenseTotal',
            'profitNet',
            'receivableTotal',
            'payableTotal',
            'receivables',
            'payables',
            'recentCashFlows',
            'expenseCategoriesChart',
            'monthLabels',
            'incomeByMonth',
            'expenseByMonth',
            'monthNames',
            'previousBalance',
            'endingBalance',
            'cashIn',
            'cashOut'
        ));
    }
}
