<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CashFlow;
use Illuminate\Support\Carbon;

class CashFlowReportService
{
    public static function buildReportData(?string $start, ?string $end): array
    {
        $start = $start ?: Carbon::now()->startOfMonth()->toDateString();
        $end = $end ?: Carbon::now()->endOfMonth()->toDateString();

        $cashAccountTypes = ['cash', 'bank', 'ewallet'];
        $cashAccountIds = Account::whereIn('type', $cashAccountTypes)->pluck('id');

        $flows = CashFlow::with('account')
            ->when($start, fn ($q) => $q->where('transaction_date', '>=', $start))
            ->when($end, fn ($q) => $q->where('transaction_date', '<=', $end))
            ->orderBy('transaction_date')
            ->get();

        $before = CashFlow::query()
            ->when($cashAccountIds->isNotEmpty(), fn ($q) => $q->whereIn('account_id', $cashAccountIds))
            ->when($start, fn ($q) => $q->where('transaction_date', '<', $start))
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income_before")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense_before")
            ->first();

        $openingBalance = Account::whereIn('type', $cashAccountTypes)->sum('initial_balance')
            + ($before->income_before ?? 0)
            - ($before->expense_before ?? 0);

        $categoryMap = [
            'operasi' => [
                'label' => 'Aktivitas Operasi',
                'reference_types' => ['pos', 'warehouse'],
            ],
            'investasi' => [
                'label' => 'Aktivitas Investasi',
                'reference_types' => ['asset'],
            ],
            'pendanaan' => [
                'label' => 'Aktivitas Pendanaan',
                'reference_types' => ['loan', 'capital'],
            ],
        ];

        $categories = collect($categoryMap)->mapWithKeys(function ($item, $key) {
            return [$key => [
                'label' => $item['label'],
                'items' => collect(),
                'income' => 0,
                'expense' => 0,
                'total' => 0,
            ]];
        })->toArray();

        $categories['others'] = [
            'label' => 'Aktivitas Lainnya',
            'items' => collect(),
            'income' => 0,
            'expense' => 0,
            'total' => 0,
        ];

        foreach ($flows as $flow) {
            $categoryKey = self::detectCategoryKey($flow, $categoryMap);

            $item = (object) [
                'id' => $flow->id,
                'reference_type' => $flow->reference_type,
                'account_name' => $flow->account->name ?? '-',
                'description' => self::buildDescription($flow),
                'income' => $flow->type === 'income' ? $flow->amount : 0,
                'expense' => $flow->type === 'expense' ? $flow->amount : 0,
                'transaction_date' => $flow->transaction_date?->toDateString(),
            ];

            $categories[$categoryKey]['items']->push($item);
            $categories[$categoryKey]['income'] += $item->income;
            $categories[$categoryKey]['expense'] += $item->expense;
        }

        foreach ($categories as $key => &$category) {
            $category['total'] = $category['income'] - $category['expense'];
        }
        unset($category);

        if ($categories['others']['items']->isEmpty()) {
            unset($categories['others']);
        }

        $totalIncome = $flows->where('type', 'income')->sum('amount');
        $totalExpense = $flows->where('type', 'expense')->sum('amount');
        $netChange = $totalIncome - $totalExpense;
        $endingBalance = $openingBalance + $netChange;

        return [
            'start' => $start,
            'end' => $end,
            'categories' => $categories,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_change' => $netChange,
            'opening_balance' => $openingBalance,
            'ending_balance' => $endingBalance,
        ];
    }

    private static function detectCategoryKey(CashFlow $flow, array $categoryMap): string
    {
        foreach ($categoryMap as $key => $meta) {
            if (in_array($flow->reference_type, $meta['reference_types'], true)) {
                return $key;
            }
        }

        return 'others';
    }

    private static function buildDescription(CashFlow $flow): string
    {
        if (!empty(trim($flow->description))) {
            return trim($flow->description);
        }

        $labels = [
            'pos' => 'Penjualan',
            'warehouse' => 'Warehouse',
            'asset' => 'Investasi',
            'loan' => 'Pinjaman',
            'capital' => 'Modal',
        ];

        $base = $labels[$flow->reference_type] ?? str_replace(['_', '-'], ' ', ucfirst($flow->reference_type ?? 'Transaksi'));
        if ($flow->type === 'income') {
            return 'Penerimaan dari ' . $base;
        }

        return 'Pembayaran ' . $base;
    }
}
