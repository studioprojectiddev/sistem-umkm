<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Account;
use App\Models\CashFlow;
use App\Exports\RekapRekeningExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class LaporanRekapRekeningController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $accountId = $request->query('account_id');
        $perPage = $request->query('per_page', '10');

        $accountsQuery = Account::query()->orderBy('name');
        if ($accountId) {
            $accountsQuery->where('id', $accountId);
        }

        if ($perPage === 'all') {
            $accounts = $accountsQuery->get();
            $accountIds = $accounts->pluck('id')->toArray();
        } else {
            $perPageNumber = (int) $perPage;
            $perPageNumber = $perPageNumber > 0 ? $perPageNumber : 10;
            $accounts = $accountsQuery->paginate($perPageNumber)->withQueryString();
            $accountIds = $accounts->getCollection()->pluck('id')->toArray();
        }

        if ($start) {
            $beforeSums = CashFlow::query()
                ->selectRaw("account_id, SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income_before")
                ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense_before")
                ->whereIn('account_id', $accountIds)
                ->where('transaction_date', '<', $start)
                ->groupBy('account_id')
                ->get()
                ->keyBy('account_id');
        } else {
            $beforeSums = collect();
        }

        $periodSums = CashFlow::query()
            ->selectRaw("account_id, SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->whereIn('account_id', $accountIds)
            ->when($start, fn ($q) => $q->where('transaction_date', '>=', $start))
            ->when($end, fn ($q) => $q->where('transaction_date', '<=', $end))
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        $accountItems = $perPage === 'all' ? $accounts : $accounts->getCollection();

        $mapped = $accountItems->map(function ($account) use ($beforeSums, $periodSums) {
$before = $beforeSums->get($account->id) ?? (object) ['income_before' => 0, 'expense_before' => 0];
        $inside = $periodSums->get($account->id) ?? (object) ['income' => 0, 'expense' => 0];

            $opening = $account->initial_balance
                + ($before->income_before ?? 0)
                - ($before->expense_before ?? 0);

            $debit = $inside->income ?? 0;
            $credit = $inside->expense ?? 0;
            $ending = $opening + $debit - $credit;

            return (object) [
                'id' => $account->id,
                'name' => $account->name,
                'opening' => $opening,
                'debit' => $debit,
                'credit' => $credit,
                'ending' => $ending,
            ];
        });

        if ($perPage === 'all') {
            $items = new LengthAwarePaginator(
                $mapped,
                $mapped->count(),
                $mapped->count(),
                1,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                ]
            );
            $items->appends($request->query());
        } else {
            $items = new LengthAwarePaginator(
                $mapped,
                $accounts->total(),
                $accounts->perPage(),
                $accounts->currentPage(),
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'query' => $request->query(),
                ]
            );
        }

        $accounts = Account::orderBy('name')->get();

        return view('umkm.laporan_rekap_rekening', compact(
            'items',
            'accounts',
            'start',
            'end',
            'accountId',
            'perPage'
        ));
    }

    public function exportExcel(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $accountId = $request->query('account_id');

        return Excel::download(
            new RekapRekeningExport($start, $end, $accountId),
            'laporan-rekap-rekening.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $accountId = $request->query('account_id');

        $items = $this->fetchItems($start, $end, $accountId);

        $pdf = Pdf::loadView('pdf.rekap_rekening', [
            'items' => $items,
            'start' => $start,
            'end' => $end,
        ]);

        return $pdf->download('laporan-rekap-rekening.pdf');
    }

    private function fetchItems(?string $start, ?string $end, $accountId = null)
    {
        $accounts = Account::when($accountId, fn ($q) => $q->where('id', $accountId))
            ->orderBy('name')
            ->get();

        return $accounts->map(fn ($account) => $this->mapAccount($account, $start, $end));
    }

    private function mapAccount(Account $account, ?string $start, ?string $end)
    {
        $accountId = $account->id;

        $before = CashFlow::query()
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income_before")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense_before")
            ->where('account_id', $accountId)
            ->when($start, fn ($q) => $q->where('transaction_date', '<', $start))
            ->first();

        $inside = CashFlow::query()
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->where('account_id', $accountId)
            ->when($start, fn ($q) => $q->where('transaction_date', '>=', $start))
            ->when($end, fn ($q) => $q->where('transaction_date', '<=', $end))
            ->first();

        $opening = $account->initial_balance
            + ($before->income_before ?? 0)
            - ($before->expense_before ?? 0);

        $debit = $inside->income ?? 0;
        $credit = $inside->expense ?? 0;
        $ending = $opening + $debit - $credit;

        return (object) [
            'id' => $accountId,
            'name' => $account->name,
            'opening' => $opening,
            'debit' => $debit,
            'credit' => $credit,
            'ending' => $ending,
        ];
    }
}
