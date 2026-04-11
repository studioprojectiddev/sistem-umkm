<?php

namespace App\Http\Controllers;

use App\Exports\BalanceSheetExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LaporanNeracaController extends Controller
{
    private function getBalanceSheetData($start = null, $end = null)
    {
        $rows = DB::table('accounting_details')
            ->join('accountings', 'accountings.id', '=', 'accounting_details.accounting_id')
            ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'accounting_details.coa_id')
            ->leftJoin('chart_of_accounts as parents', 'parents.id', '=', 'chart_of_accounts.parent_id')
            ->where('accountings.status_accounting', 'posting')
            ->when($start, fn ($q) => $q->whereDate('accountings.created_date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('accountings.created_date', '<=', $end))
            ->selectRaw('chart_of_accounts.id, chart_of_accounts.code, chart_of_accounts.name, chart_of_accounts.type, chart_of_accounts.parent_id, parents.name as parent_name, COALESCE(SUM(accounting_details.debit), 0) AS debit_sum, COALESCE(SUM(accounting_details.credit), 0) AS credit_sum')
            ->groupBy('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.type', 'chart_of_accounts.parent_id', 'parents.name')
            ->orderBy('chart_of_accounts.code')
            ->get()
            ->map(function ($item) {
                $debitSum = (float) $item->debit_sum;
                $creditSum = (float) $item->credit_sum;
                $isAsset = $item->type === 'asset';
                $saldo = $isAsset
                    ? $debitSum - $creditSum
                    : $creditSum - $debitSum;

                return (object) [
                    'id' => $item->id,
                    'code' => $item->code,
                    'name' => $item->name,
                    'type' => $item->type,
                    'parent_name' => $item->parent_name,
                    'debit' => $saldo >= 0 ? $saldo : 0,
                    'credit' => $saldo < 0 ? abs($saldo) : 0,
                    'saldo' => $saldo,
                ];
            });

        $assetGroups = $this->buildSection($rows->where('type', 'asset'), 'asset');
        $liabilityGroups = $this->buildSection($rows->where('type', 'liability'), 'liability');
        $equityGroups = $this->buildSection($rows->where('type', 'equity'), 'equity');
        $revenueGroups = $this->buildSection($rows->where('type', 'revenue'), 'equity');

        $equityGroups = $this->mergeGroups($equityGroups, $revenueGroups);

        return [
            'assets' => $assetGroups,
            'liabilities' => $liabilityGroups,
            'equities' => $equityGroups,
            'total_debit' => $rows->sum('debit'),
            'total_credit' => $rows->sum('credit'),
        ];
    }

    private function buildSection($rows, $type)
    {
        $labels = match ($type) {
            'asset' => ['Kas & Bank', 'Akun Piutang', 'Aktiva Lancar Lainnya', 'Aktiva Tetap'],
            'liability' => ['Akun Hutang', 'Liabilitas Lainnya'],
            default => ['Ekuitas', 'Pendapatan Periode Ini'],
        };

        $groups = [];

        foreach ($rows as $row) {
            $label = $this->determineGroupLabel($row, $type);

            if (!isset($groups[$label])) {
                $groups[$label] = [
                    'label' => $label,
                    'items' => [],
                    'subtotal_debit' => 0,
                    'subtotal_credit' => 0,
                ];
            }

            $groups[$label]['items'][] = $row;
            $groups[$label]['subtotal_debit'] += $row->debit;
            $groups[$label]['subtotal_credit'] += $row->credit;
        }

        $orderedGroups = [];

        foreach ($labels as $label) {
            if (isset($groups[$label])) {
                $orderedGroups[] = $groups[$label];
                unset($groups[$label]);
            }
        }

        foreach ($groups as $group) {
            $orderedGroups[] = $group;
        }

        return $orderedGroups;
    }

    private function determineGroupLabel($row, $type)
    {
        $name = strtolower($row->parent_name . ' ' . $row->name);

        if ($type === 'asset') {
            if (str_contains($name, 'kas') || str_contains($name, 'bank')) {
                return 'Kas & Bank';
            }

            if (str_contains($name, 'piutang')) {
                return 'Akun Piutang';
            }

            if (str_contains($name, 'aset tetap') || str_contains($name, 'aktiva tetap')) {
                return 'Aktiva Tetap';
            }

            return 'Aktiva Lancar Lainnya';
        }

        if ($type === 'liability') {
            if (str_contains($name, 'hutang')) {
                return 'Akun Hutang';
            }

            return 'Liabilitas Lainnya';
        }

        if ($row->type === 'revenue') {
            return 'Pendapatan Periode Ini';
        }

        return 'Ekuitas';
    }

    private function mergeGroups(array $first, array $second)
    {
        $merged = [];

        foreach (array_merge($first, $second) as $group) {
            if (!isset($merged[$group['label']])) {
                $merged[$group['label']] = $group;
                continue;
            }

            $merged[$group['label']]['items'] = array_merge($merged[$group['label']]['items'], $group['items']);
            $merged[$group['label']]['subtotal_debit'] += $group['subtotal_debit'];
            $merged[$group['label']]['subtotal_credit'] += $group['subtotal_credit'];
        }

        return array_values($merged);
    }

    public function index(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        $data = $this->getBalanceSheetData($start, $end);

        return view('umkm.laporan_neraca', compact('data', 'start', 'end'));
    }

    public function exportExcel(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        return Excel::download(new BalanceSheetExport($start, $end), 'laporan_neraca.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        $data = $this->getBalanceSheetData($start, $end);

        $pdf = Pdf::loadView('pdf.laporan_neraca', compact('data', 'start', 'end'));

        return $pdf->download('laporan_neraca.pdf');
    }
}