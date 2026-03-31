<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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

        $query = CashFlow::query()
            ->select([
                'cash_flows.id',
                'cash_flows.type',
                'cash_flows.category_id',
                'cash_flows.account_id',
                'cash_flows.amount',
                'cash_flows.amount as dibayar',
                'cash_flows.description',
                'cash_flows.transaction_date',
                'cash_flows.reference_type',
                'cash_flows.reference_id',
                'cash_flows.created_by',
                DB::raw('
                    CASE
                        WHEN cash_flows.reference_type = \'pos\' THEN COALESCE(transactions.total, cash_flows.amount)
                        WHEN cash_flows.reference_type = \'warehouse\' THEN COALESCE(warehouse_stock_logs.total, cash_flows.amount)
                        ELSE cash_flows.amount
                    END AS nominal
                ')
            ])
            ->leftJoin('transactions', function($join) {
                $join->on('cash_flows.reference_id', '=', 'transactions.id')
                    ->where('cash_flows.reference_type', '=', DB::raw("'pos'"));
            })
            ->leftJoin('warehouse_stock_logs', function($join) {
                $join->on('cash_flows.reference_id', '=', 'warehouse_stock_logs.id')
                    ->where('cash_flows.reference_type', '=', DB::raw("'warehouse'"));
            });

        // ================= FILTER =================

        if ($month) {
            $query->whereMonth('cash_flows.transaction_date', $month);
        }

        if ($year) {
            $query->whereYear('cash_flows.transaction_date', $year);
        }

        if ($type) {
            $query->where('cash_flows.type', $type);
        }

        $cashflows = $query->orderByDesc('cash_flows.transaction_date')->paginate(20);

        $totalIncome = (clone $query)->where('cash_flows.type','income')->sum('cash_flows.amount');
        $totalExpense = (clone $query)->where('cash_flows.type','expense')->sum('cash_flows.amount');

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

    public function transferIndex(Request $request)
    {
        // ================= DATA REKENING =================
        $accounts = Account::get();

        // ================= DATA TRANSFER =================
        $query = AccountTransfer::with(['fromAccount','toAccount']);

        $startDate = $request->start_date ?? \Carbon\Carbon::now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date ?? \Carbon\Carbon::now()->toDateString();

        $query = AccountTransfer::with(['fromAccount','toAccount'])
            ->whereBetween('transfer_date', [$startDate, $endDate]);

        $transfers = $query
            ->orderByDesc('transfer_date')
            ->paginate(10)
            ->withQueryString();

        // ================= TOTAL SEMUA TRANSFER =================
        $totalTransfer = DB::table('account_transfers')
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->sum('amount');

        // ================= TOTAL PER ACCOUNT (KHUSUS TRANSFER) =================
        $out = DB::table('account_transfers')
            ->select(
                'from_account_id as account_id',
                DB::raw('SUM(amount) * -1 as total')
            )
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->groupBy('from_account_id');

        $in = DB::table('account_transfers')
            ->select(
                'to_account_id as account_id',
                DB::raw('SUM(amount) as total')
            )
            ->whereBetween('transfer_date', [$startDate, $endDate])
            ->groupBy('to_account_id');

        $accountTransferTotals = DB::table(DB::raw("({$out->toSql()} UNION ALL {$in->toSql()}) as t"))
            ->mergeBindings($out)
            ->mergeBindings($in)
            ->select('account_id', DB::raw('SUM(total) as balance'))
            ->groupBy('account_id')
            ->pluck('balance', 'account_id');

        // ================= RETURN VIEW =================
        return view('umkm.transaction.transfer', [
            'accounts' => $accounts,
            'transfers' => $transfers,
            'accountTotals' => $accountTransferTotals,
            'totalTransfer' => $totalTransfer,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function storeTransferTransaksi(Request $request)
    {
        DB::beginTransaction();

        try {

            // ================= VALIDASI =================
            $validator = \Validator::make($request->all(), [
                'from_account_id' => 'required|exists:accounts,id',
                'to_account_id'   => 'required|exists:accounts,id',
                'amount'          => 'required|numeric|min:1',
                'transfer_date'   => 'required|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ]);
            }

            $validated = $validator->validated();

            // ❌ rekening sama
            if ($validated['from_account_id'] == $validated['to_account_id']) {
                throw new \Exception('Rekening tidak boleh sama');
            }

            // ================= CEK SALDO =================
            $balance = DB::table('cash_flows')
                ->where('account_id', $validated['from_account_id'])
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN type='income' THEN amount ELSE 0 END),0)
                    -
                    COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END),0)
                    as balance
                ")
                ->value('balance');

            if ($balance < $validated['amount']) {
                throw new \Exception('Saldo tidak mencukupi');
            }

            // ================= SIMPAN =================
            $transfer = AccountTransfer::create([
                'from_account_id' => $validated['from_account_id'],
                'to_account_id'   => $validated['to_account_id'],
                'amount'          => $validated['amount'],
                'transfer_date'   => $validated['transfer_date'],
                'created_by'      => auth()->id()
            ]);

            // OUT
            CashFlow::create([
                'type' => 'expense',
                'amount' => $validated['amount'],
                'account_id' => $validated['from_account_id'],
                'transaction_date' => $validated['transfer_date'],
                'description' => 'Transfer keluar',
                'reference_type' => 'transfer',
                'reference_id' => $transfer->id,
                'created_by' => auth()->id()
            ]);

            // IN
            CashFlow::create([
                'type' => 'income',
                'amount' => $validated['amount'],
                'account_id' => $validated['to_account_id'],
                'transaction_date' => $validated['transfer_date'],
                'description' => 'Transfer masuk',
                'reference_type' => 'transfer',
                'reference_id' => $transfer->id,
                'created_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transfer berhasil'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
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

    public function processOcr(Request $request)
    {
        try {

            // ======================
            // VALIDASI
            // ======================
            $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            // ======================
            // SIMPAN FILE
            // ======================
            $path = $request->file('image')->store('receipts', 'public');
            $fullPath = storage_path('app/public/' . $path);

            // ======================
            // OCR (TESERACT)
            // ======================
            $command = "tesseract " 
                . escapeshellarg($fullPath) 
                . " stdout --psm 6 -l eng 2>&1";

            $text = shell_exec($command);

            if (!$text) {
                throw new \Exception('OCR gagal membaca gambar');
            }

            // ======================
            // CLEAN TEXT
            // ======================
            $text = strtolower($text);
            $text = preg_replace('/\s+/', ' ', $text);

            // ======================
            // TOTAL (SMART VERSION 🔥)
            // ======================
            $total = 0;

            // 🔥 PRIORITAS 1: TOTAL PEMBAYARAN
            preg_match('/total\s+pembayaran[^0-9]*([0-9\.\,]+)/i', $text, $match1);

            if (!empty($match1[1])) {
                $total = (int) preg_replace('/[^0-9]/', '', $match1[1]);
            }

            // 🔥 PRIORITAS 2: GRAND TOTAL / TOTAL BAYAR
            if ($total == 0) {
                preg_match('/(grand\s+total|total\s+bayar)[^0-9]*([0-9\.\,]+)/i', $text, $match2);

                if (!empty($match2[2])) {
                    $total = (int) preg_replace('/[^0-9]/', '', $match2[2]);
                }
            }

            // 🔥 PRIORITAS 3: TOTAL SAJA
            if ($total == 0) {
                preg_match('/total[^0-9]*([0-9\.\,]+)/i', $text, $match3);

                if (!empty($match3[1])) {
                    $total = (int) preg_replace('/[^0-9]/', '', $match3[1]);
                }
            }

            // 🔥 PRIORITAS 4: ANGKA TERAKHIR (fallback)
            if ($total == 0) {
                preg_match_all('/rp\s?([0-9\.\,]+)/i', $text, $matches);

                if (!empty($matches[1])) {
                    $last = end($matches[1]);
                    $total = (int) preg_replace('/[^0-9]/', '', $last);
                }
            }

            // ======================
            // DATE (SMART VERSION 🔥)
            // ======================
            $date = now()->format('Y-m-d'); // default hari ini

            // format: 21/03/2026 atau 21-03-2026
            preg_match('/(\d{2}[\/\-]\d{2}[\/\-]\d{4})/', $text, $dateMatch1);

            // format: 2026-03-21
            preg_match('/(\d{4}[\/\-]\d{2}[\/\-]\d{2})/', $text, $dateMatch2);

            // fallback: angka 8 digit (21032026)
            preg_match('/(\d{8})/', $text, $dateMatch3);

            if (!empty($dateMatch1[1])) {

                try {
                    $date = \Carbon\Carbon::createFromFormat(
                        'd-m-Y',
                        str_replace('/', '-', $dateMatch1[1])
                    )->format('Y-m-d');
                } catch (\Exception $e) {}

            } elseif (!empty($dateMatch2[1])) {

                try {
                    $date = \Carbon\Carbon::parse($dateMatch2[1])->format('Y-m-d');
                } catch (\Exception $e) {}

            } elseif (!empty($dateMatch3[1])) {

                try {
                    $raw = $dateMatch3[1];

                    $day = substr($raw, 0, 2);
                    $month = substr($raw, 2, 2);
                    $year = substr($raw, 4, 4);

                    $date = \Carbon\Carbon::createFromFormat(
                        'd-m-Y',
                        "$day-$month-$year"
                    )->format('Y-m-d');

                } catch (\Exception $e) {}
            }

            // ======================
            // RESPONSE
            // ======================
            return response()->json([
                'status' => 'success',
                'text' => $text,
                'total' => $total,
                'date' => $date,
                'image_url' => asset('storage/' . $path)
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function storeFromOcr(Request $request)
    {
        DB::beginTransaction();

        try {

            $request->validate([
                'amount' => 'required|numeric|min:1',
                'transaction_date' => 'required|date',
                'account_id' => 'required|exists:accounts,id'
            ]);

            // ======================
            // SIMPAN KE CASH FLOW
            // ======================
            CashFlow::create([
                'type' => 'expense', // default dari nota
                'amount' => $request->amount,
                'account_id' => $request->account_id,
                'transaction_date' => $request->transaction_date,
                'description' => 'OCR Nota',
                'reference_type' => 'ocr',
                'reference_id' => null,
                'created_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil disimpan'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function bank()
    {
        $accounts = Account::all();

        // HITUNG SALDO
        $balances = DB::table('cash_flows')
            ->select(
                'account_id',
                DB::raw("
                    COALESCE(SUM(CASE WHEN type='income' THEN amount ELSE 0 END),0)
                    -
                    COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END),0)
                    as balance
                ")
            )
            ->groupBy('account_id')
            ->pluck('balance', 'account_id');

        // TOTAL SEMUA
        $totalBalance = $balances->sum();

        return view('umkm.transaction.bank', compact(
            'accounts',
            'balances',
            'totalBalance'
        ));
    }

    public function storeAccount(Request $request)
    {
        try {

            $name = $request->input('name');

            if(!$name){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nama kosong'
                ]);
            }

            Account::create([
                'name' => $name,
                'type' => 'cash', // default
                'type_account' => null,
                'initial_balance' => 0
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Rekening berhasil ditambahkan'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateAccount(Request $request, $id)
    {
        try {

            $account = Account::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:100'
            ]);

            // 🚨 CEK SUDAH DIPAKAI ATAU BELUM
            if ($account->cashflows()->exists()) {

                // 🔒 HANYA BOLEH UBAH NAMA
                $account->update([
                    'name' => $request->name
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Nama rekening berhasil diupdate (type & saldo terkunci karena sudah ada transaksi)'
                ]);

            } else {

                // ✅ BEBAS EDIT
                $account->update([
                    'name' => $request->name,
                    'type' => $request->type,
                    'initial_balance' => $request->initial_balance ?? 0,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Rekening berhasil diupdate'
                ]);
            }

        } catch (\Throwable $e) {

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function deleteAccount($id)
    {
        try {

            $account = Account::findOrFail($id);

            // 🚨 CEK ADA TRANSAKSI ATAU TIDAK
            if ($account->cashflows()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Rekening tidak bisa dihapus karena sudah digunakan di transaksi'
                ]);
            }

            $account->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Rekening berhasil dihapus'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function history(){
        return view('umkm.transaction.history');
    }
}
