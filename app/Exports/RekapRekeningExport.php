<?php

namespace App\Exports;

use App\Models\Account;
use App\Models\CashFlow;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RekapRekeningExport implements FromView
{
    protected $start;
    protected $end;
    protected $accountId;

    public function __construct($start = null, $end = null, $accountId = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->accountId = $accountId;
    }

    public function view(): View
    {
        $accounts = Account::when($this->accountId, fn ($q) => $q->where('id', $this->accountId))
            ->orderBy('name')
            ->get();

        $items = $accounts->map(fn ($account) => $this->mapAccount($account));

        return view('exports.rekap_rekening', [
            'items' => $items,
        ]);
    }

    private function mapAccount(Account $account)
    {
        $accountId = $account->id;

        if ($this->start) {
            $before = CashFlow::query()
                ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income_before")
                ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense_before")
                ->where('account_id', $accountId)
                ->where('transaction_date', '<', $this->start)
                ->first();
        } else {
            $before = (object) ['income_before' => 0, 'expense_before' => 0];
        }

        $inside = CashFlow::query()
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->where('account_id', $accountId)
            ->when($this->start, fn ($q) => $q->where('transaction_date', '>=', $this->start))
            ->when($this->end, fn ($q) => $q->where('transaction_date', '<=', $this->end))
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
