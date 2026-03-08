<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\CashFlow;
use App\Models\Account;
use App\Models\CashflowCategory;
use App\Models\CashflowClosing;
use App\Models\AccountTransfer;
use App\Models\AccountOpening;
use App\Exports\CashflowExport;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    public function income(Request $request)
    {
        $month = $request->month;
        $year  = $request->year ?? now()->year;
        $type  = $request->type;

        $query = CashFlow::query();

        // ================= FILTER =================

        if ($month) {
            $query->whereMonth('transaction_date', $month);
        }

        if ($year) {
            $query->whereYear('transaction_date', $year);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $cashflows = $query->orderByDesc('transaction_date')->paginate(20);

        $totalIncome = (clone $query)->where('type','income')->sum('amount');
        $totalExpense = (clone $query)->where('type','expense')->sum('amount');

        // ================= GRAFIK CASHFLOW =================

        $rawMonthly = CashFlow::selectRaw("
            EXTRACT(MONTH FROM transaction_date) as month,
            SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as income,
            SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as expense
        ")
        ->when($year, fn($q)=>$q->whereYear('transaction_date',$year))
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

        // ================= DONUT KATEGORI =================

        $expenseByCategory = CashFlow::where('cash_flows.type','expense')
            ->when($year, fn($q)=>$q->whereYear('transaction_date',$year))
            ->when($month, fn($q)=>$q->whereMonth('transaction_date',$month))
            ->join('cashflow_categories','cash_flows.category_id','=','cashflow_categories.id')
            ->select(
                'cashflow_categories.name as category',
                DB::raw('SUM(cash_flows.amount) as total')
            )
            ->groupBy('cashflow_categories.name')
            ->orderByDesc('total')
            ->get();

        // ================= PERBANDINGAN BULAN =================

        $currentMonth = now()->month;
        $currentYear  = now()->year;

        $lastMonth = now()->subMonth()->month;
        $lastMonthYear = now()->subMonth()->year;

        $incomeCurrent = CashFlow::where('type','income')
            ->whereMonth('transaction_date',$currentMonth)
            ->whereYear('transaction_date',$currentYear)
            ->sum('amount');

        $incomeLast = CashFlow::where('type','income')
            ->whereMonth('transaction_date',$lastMonth)
            ->whereYear('transaction_date',$lastMonthYear)
            ->sum('amount');

        $expenseCurrent = CashFlow::where('type','expense')
            ->whereMonth('transaction_date',$currentMonth)
            ->whereYear('transaction_date',$currentYear)
            ->sum('amount');

        $expenseLast = CashFlow::where('type','expense')
            ->whereMonth('transaction_date',$lastMonth)
            ->whereYear('transaction_date',$lastMonthYear)
            ->sum('amount');

        $incomeGrowth = $incomeLast > 0
            ? (($incomeCurrent - $incomeLast) / $incomeLast) * 100
            : 0;

        $expenseGrowth = $expenseLast > 0
            ? (($expenseCurrent - $expenseLast) / $expenseLast) * 100
            : 0;

        // ================= SALDO PER REKENING =================

        $accounts = Account::all();

        $accountBalances = $accounts->map(function($acc) use ($month,$year){

            $opening = \App\Models\AccountOpening::where('account_id',$acc->id)
                ->where('month',$month ?? now()->month)
                ->where('year',$year ?? now()->year)
                ->value('opening_balance');

            $opening = $opening ?? $acc->initial_balance;

            $income = CashFlow::where('account_id',$acc->id)
                ->where('type','income')
                ->when($month, fn($q)=>$q->whereMonth('transaction_date',$month))
                ->when($year, fn($q)=>$q->whereYear('transaction_date',$year))
                ->sum('amount');

            $expense = CashFlow::where('account_id',$acc->id)
                ->where('type','expense')
                ->when($month, fn($q)=>$q->whereMonth('transaction_date',$month))
                ->when($year, fn($q)=>$q->whereYear('transaction_date',$year))
                ->sum('amount');

            $acc->balance = $opening + $income - $expense;

            return $acc;
        });

        // ================= DATA TAMBAHAN =================

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

    private function isMonthLocked($date)
    {
        $month = \Carbon\Carbon::parse($date)->month;
        $year  = \Carbon\Carbon::parse($date)->year;

        return \App\Models\CashflowClosing::where('month',$month)
            ->where('year',$year)
            ->exists();
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

        if ($this->isMonthLocked($request->transaction_date)) {
            return back()->with('error','Bulan ini sudah di closing dan tidak bisa diubah.');
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

        if ($this->isMonthLocked($cashflow->transaction_date)) {
            return back()->with('error','Data bulan ini sudah di closing.');
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
            'month'=>'required|integer|min:1|max:12',
            'year'=>'required|integer'
        ]);

        $month = $request->month;
        $year  = $request->year;

        // 🔒 Cegah double closing
        $alreadyClosed = CashflowClosing::where('month',$month)
            ->where('year',$year)
            ->exists();

        if($alreadyClosed){
            return back()->with('warning','Bulan ini sudah pernah dikunci.');
        }

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

                // 🔒 Cegah duplicate opening balance
                $openingExists = AccountOpening::where('account_id',$acc->id)
                    ->where('month',$nextDate->month)
                    ->where('year',$nextDate->year)
                    ->exists();

                if(!$openingExists){
                    AccountOpening::create([
                        'account_id'=>$acc->id,
                        'month'=>$nextDate->month,
                        'year'=>$nextDate->year,
                        'opening_balance'=>$endingBalance
                    ]);
                }

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

    // private function isLocked($date)
    // {
    //     $month = \Carbon\Carbon::parse($date)->month;
    //     $year  = \Carbon\Carbon::parse($date)->year;

    //     return \App\Models\CashflowClosing::where('month',$month)
    //         ->where('year',$year)
    //         ->exists();
    // }

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

        // cek apakah transaksi lama sudah di lock
        if ($this->isMonthLocked($cashflow->transaction_date)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bulan ini sudah di closing dan tidak bisa diubah.'
            ]);
        }

        // validasi
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:cashflow_categories,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        // cek apakah tanggal baru masuk bulan yang sudah di lock
        if ($this->isMonthLocked($validated['transaction_date'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tanggal yang dipilih berada pada bulan yang sudah di closing.'
            ]);
        }

        $cashflow->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi berhasil diperbarui.'
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
