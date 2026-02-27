<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\CashFlow;
use App\Models\Account;
use App\Models\CashflowCategory;
use App\Models\CashflowClosing;
use App\Exports\CashflowExport;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    public function income(Request $request){
        $query = CashFlow::query();

        // 🔹 Filter bulan
        if ($request->month) {
            $query->whereMonth('transaction_date', $request->month);
        }

        // 🔹 Filter tahun
        if ($request->year) {
            $query->whereYear('transaction_date', $request->year);
        }

        // 🔹 Filter tipe
        if ($request->type) {
            $query->where('type', $request->type);
        }

        $cashflows = $query->orderByDesc('transaction_date')->paginate(20);

        $totalIncome = $query->clone()->where('type','income')->sum('amount');
        $totalExpense = $query->clone()->where('type','expense')->sum('amount');

        // 🔹 Grafik 12 bulan
        $rawMonthly = CashFlow::selectRaw("
            EXTRACT(MONTH FROM transaction_date) as month,
            SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as income,
            SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as expense
        ")
        ->whereYear('transaction_date', now()->year)
        ->groupByRaw("EXTRACT(MONTH FROM transaction_date)")
        ->get()
        ->keyBy(fn($m)=> (int)$m->month);

        $monthly = collect();

        for($i=1;$i<=12;$i++){
            $monthly->push((object)[
                'month'=>$i,
                'income'=>$rawMonthly[$i]->income ?? 0,
                'expense'=>$rawMonthly[$i]->expense ?? 0,
            ]);
        }

        // 🔹 Donut Chart - Pengeluaran per Kategori
        $expenseByCategory = CashFlow::where('cash_flows.type','expense')
        ->whereYear('transaction_date', now()->year)
        ->join('cashflow_categories','cash_flows.category_id','=','cashflow_categories.id')
        ->select(
            'cashflow_categories.name as category',
            DB::raw('SUM(cash_flows.amount) as total')
        )
        ->groupBy('cashflow_categories.name')
        ->orderByDesc('total')
        ->get();

        // 🔹 Bulan ini
        $currentMonth = now()->month;
        $currentYear  = now()->year;

        // 🔹 Bulan lalu
        $lastMonth = now()->subMonth()->month;
        $lastMonthYear = now()->subMonth()->year;

        // Income bulan ini
        $incomeCurrent = CashFlow::where('type','income')
            ->whereMonth('transaction_date',$currentMonth)
            ->whereYear('transaction_date',$currentYear)
            ->sum('amount');

        // Income bulan lalu
        $incomeLast = CashFlow::where('type','income')
            ->whereMonth('transaction_date',$lastMonth)
            ->whereYear('transaction_date',$lastMonthYear)
            ->sum('amount');

        // Expense bulan ini
        $expenseCurrent = CashFlow::where('type','expense')
            ->whereMonth('transaction_date',$currentMonth)
            ->whereYear('transaction_date',$currentYear)
            ->sum('amount');

        // Expense bulan lalu
        $expenseLast = CashFlow::where('type','expense')
            ->whereMonth('transaction_date',$lastMonth)
            ->whereYear('transaction_date',$lastMonthYear)
            ->sum('amount');

        // Hitung persen perubahan
        $incomeGrowth = $incomeLast > 0
            ? (($incomeCurrent - $incomeLast) / $incomeLast) * 100
            : 0;

        $expenseGrowth = $expenseLast > 0
            ? (($expenseCurrent - $expenseLast) / $expenseLast) * 100
            : 0;

        $accounts = Account::all();

        $accountBalances = Account::with('cashflows')->get()->map(function($acc){

            $currentMonth = now()->month;
            $currentYear  = now()->year;

            // 🔹 Ambil opening balance bulan ini
            $opening = \App\Models\AccountOpening::where('account_id',$acc->id)
                ->where('month',$currentMonth)
                ->where('year',$currentYear)
                ->value('opening_balance');

            // Kalau belum ada opening (berarti belum pernah closing)
            $opening = $opening ?? $acc->initial_balance;

            // Income bulan ini
            $income = $acc->cashflows
                ->where('type','income')
                ->whereBetween('transaction_date',[
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ])
                ->sum('amount');

            // Expense bulan ini
            $expense = $acc->cashflows
                ->where('type','expense')
                ->whereBetween('transaction_date',[
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ])
                ->sum('amount');

            $acc->balance = $opening + $income - $expense;

            return $acc;
        });

        $categories = CashflowCategory::where('is_active',true)->get();

        $lockedMonths = CashflowClosing::all();

        return view('umkm.transaction.income', compact(
            'cashflows',
            'totalIncome',
            'totalExpense',
            'expenseByCategory',
            'monthly',
            'incomeCurrent',
            'incomeLast',
            'expenseCurrent',
            'expenseLast',
            'incomeGrowth',
            'expenseGrowth',
            'accounts',
            'accountBalances',
            'categories',
            'lockedMonths'
        ));
    }

    public function store_income(Request $request)
    {
        $request->validate([
            'type'=>'required|in:income,expense',
            'category_id'=>'required|exists:cashflow_categories,id',
            'account_id'=>'required|exists:accounts,id',
            'amount'=>'required|numeric|min:1',
            'transaction_date'=>'required|date'
        ]);

        if ($this->isLocked($request->transaction_date)) {
            return back()->with('error','Bulan ini sudah dikunci. Tidak bisa tambah transaksi.');
        }

        CashFlow::create([
            'type'=>$request->type,
            'category_id'=>$request->category_id,
            'account_id'=>$request->account_id,
            'amount'=>$request->amount,
            'transaction_date'=>$request->transaction_date,
            'description'=>$request->description,
            'created_by'=>auth()->id()
        ]);

        return back()->with('success','Transaksi berhasil ditambahkan');
    }

    public function destroy_income($id)
    {
        $cashflow = CashFlow::findOrFail($id);

        if ($this->isLocked($cashflow->transaction_date)) {
            return back()->with('error','Bulan ini sudah dikunci. Tidak bisa hapus.');
        }

        $cashflow->delete();

        return back()->with('success','Data berhasil dihapus');
    }

    public function trash()
    {
        $trashed = CashFlow::onlyTrashed()
            ->with(['category','account','creator'])
            ->orderByDesc('deleted_at')
            ->paginate(20);

        return view('umkm.transaction.trash', compact('trashed'));
    }

    public function restore($id)
    {
        $cashflow = CashFlow::withTrashed()->findOrFail($id);

        $cashflow->restore();

        return back()->with('success','Transaksi berhasil direstore');
    }

    public function forceDelete($id)
    {
        $cashflow = CashFlow::withTrashed()->findOrFail($id);

        $cashflow->forceDelete();

        return back()->with('success','Transaksi dihapus permanen');
    }

    public function transferIndex()
    {
        $accounts = Account::where('is_active',true)->get();
        $transfers = AccountTransfer::with(['fromAccount','toAccount'])
            ->orderByDesc('transfer_date')
            ->paginate(15);

        return view('umkm.transaction.transfer',compact('accounts','transfers'));
    }

    public function closeMonth(Request $request)
    {
        $request->validate([
            'month'=>'required',
            'year'=>'required'
        ]);

        $month = $request->month;
        $year  = $request->year;

        DB::transaction(function() use ($month,$year){

            // 🔹 Simpan closing
            CashflowClosing::create([
                'month'=>$month,
                'year'=>$year,
                'closed_at'=>now(),
                'closed_by'=>auth()->id()
            ]);

            // 🔹 Ambil semua rekening
            $accounts = Account::all();

            foreach($accounts as $acc){

                // Income bulan ini
                $income = CashFlow::where('account_id',$acc->id)
                    ->where('type','income')
                    ->whereMonth('transaction_date',$month)
                    ->whereYear('transaction_date',$year)
                    ->sum('amount');

                // Expense bulan ini
                $expense = CashFlow::where('account_id',$acc->id)
                    ->where('type','expense')
                    ->whereMonth('transaction_date',$month)
                    ->whereYear('transaction_date',$year)
                    ->sum('amount');

                // Transfer
                $transferOut = AccountTransfer::where('from_account_id',$acc->id)
                    ->whereMonth('transfer_date',$month)
                    ->whereYear('transfer_date',$year)
                    ->sum('amount');

                $transferIn = AccountTransfer::where('to_account_id',$acc->id)
                    ->whereMonth('transfer_date',$month)
                    ->whereYear('transfer_date',$year)
                    ->sum('amount');

                // Saldo akhir bulan ini
                $endingBalance =
                    $acc->initial_balance
                    + $income
                    - $expense
                    - $transferOut
                    + $transferIn;

                // Hitung bulan berikutnya
                $nextDate = \Carbon\Carbon::create($year,$month,1)->addMonth();

                AccountOpening::create([
                    'account_id'=>$acc->id,
                    'month'=>$nextDate->month,
                    'year'=>$nextDate->year,
                    'opening_balance'=>$endingBalance
                ]);
            }

        });

        return back()->with('success','Bulan berhasil dikunci & opening balance dibuat');
    }

    public function unlockMonth(Request $request)
    {
        // 🔒 Batasi hanya admin
        // if (!auth()->user()->hasRole('superadmin')) {
        //     abort(403,'Tidak memiliki akses');
        // }
        if (auth()->id() != 1) abort(403);

        CashflowClosing::where('month',$request->month)
            ->where('year',$request->year)
            ->delete();

        return back()->with('success','Bulan berhasil dibuka kembali');
    }

    public function storeTransfer(Request $request)
    {
        $request->validate([
            'from_account_id'=>'required|different:to_account_id',
            'to_account_id'=>'required',
            'amount'=>'required|numeric|min:1',
            'transfer_date'=>'required|date'
        ]);

        DB::transaction(function() use ($request){

            AccountTransfer::create([
                'from_account_id'=>$request->from_account_id,
                'to_account_id'=>$request->to_account_id,
                'amount'=>$request->amount,
                'transfer_date'=>$request->transfer_date,
                'description'=>$request->description,
                'created_by'=>auth()->id()
            ]);

        });

        return back()->with('success','Transfer berhasil');
    }

    private function isLocked($date)
    {
        $month = \Carbon\Carbon::parse($date)->month;
        $year  = \Carbon\Carbon::parse($date)->year;

        return \App\Models\CashflowClosing::where('month',$month)
            ->where('year',$year)
            ->exists();
    }

    public function export_income(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;

        return Excel::download(
            new CashflowExport($month,$year),
            "cashflow_{$month}_{$year}.xlsx"
        );
    }

    public function update_income(Request $request, $id)
    {
        $cashflow = CashFlow::findOrFail($id);

        $cashflow->update([
            'type' => $request->type,
            'category_id' => $request->category_id,
            'account_id' => $request->account_id,
            'amount' => $request->amount,
            'transaction_date' => $request->transaction_date,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function upload(){
        return view('umkm.transaction.upload');
    }

    public function bank(){
        return view('umkm.transaction.bank');
    }

    public function history(){
        return view('umkm.transaction.history');
    }
}
