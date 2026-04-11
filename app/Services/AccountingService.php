<?php

namespace App\Services;

use App\Models\Accounting;
use App\Models\AccountingDetail;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountingService
{
    /**
     * Generate nomor jurnal dengan format: JR-YYYYMMDD-XXXX
     * XXXX = sequential number untuk tanggal tersebut
     */
    protected function generateJournalNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = 'JR-' . $today . '-';

        // Hitung jumlah jurnal hari ini
        $countToday = Accounting::whereRaw("DATE(created_date) = ?", [now()->format('Y-m-d')])
            ->count();

        $sequence = str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);

        return $prefix . $sequence;
    }

    /**
     * Ambil COA ID berdasarkan code
     * Helper untuk kemudahan akses akun
     */
    protected function getCoaIdByCode(string $code): int
    {
        $coa = ChartOfAccount::where('code', $code)
            ->where('is_active', true)
            ->firstOrFail();

        return $coa->id;
    }

    /**
     * Validasi apakah sudah ada jurnal untuk reference yang sama
     * Mencegah double posting
     */
    protected function checkDuplicatePosting(string $referenceType, int $referenceId): void
    {
        $exists = Accounting::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('is_reversal', false)
            ->exists();

        if ($exists) {
            throw new \Exception(
                "Jurnal untuk {$referenceType} #{$referenceId} sudah dibuat (tidak boleh double posting)"
            );
        }
    }

    /**
     * Helper function untuk membuat jurnal
     * 
     * @param string $referenceType Jenis transaksi (pos, warehouse, cashflow, dll)
     * @param int $referenceId ID transaksi asal
     * @param array $details Detail jurnal
     *    Format: [
     *      ['coa_id' => 1, 'debit' => 10000, 'credit' => 0, 'description' => 'xxx'],
     *      ['coa_id' => 2, 'debit' => 0, 'credit' => 10000, 'description' => 'yyy'],
     *    ]
     * @return Accounting
     */
    public function createJournal(string $referenceType, int $referenceId, array $details): Accounting
    {
        return DB::transaction(function () use ($referenceType, $referenceId, $details) {
            // 1. Validasi
            $this->validateJournalDetails($details);

            // 2. Cek double posting
            $this->checkDuplicatePosting($referenceType, $referenceId);

            // 3. Hitung total debit & credit
            $totalDebit = collect($details)->sum('debit');
            $totalCredit = collect($details)->sum('credit');

            // 4. Buat header jurnal
            $journalNumber = $this->generateJournalNumber();

            $accounting = Accounting::create([
                'journal_number' => $journalNumber,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'status_accounting' => 'posting',
                'is_reversal' => false,
                'created_by' => auth()->check() ? auth()->id() : 1,
                'created_date' => now(),
            ]);

            // 5. Insert detail jurnal
            foreach ($details as $detail) {
                AccountingDetail::create([
                    'accounting_id' => $accounting->id,
                    'coa_id' => $detail['coa_id'],
                    'debit' => $detail['debit'] ?? 0,
                    'credit' => $detail['credit'] ?? 0,
                    'description' => $detail['description'] ?? null,
                    'created_by' => auth()->check() ? auth()->id() : 1,
                    'created_date' => now(),
                ]);
            }

            Log::info("Jurnal {$journalNumber} berhasil dibuat untuk {$referenceType} #{$referenceId}");

            return $accounting;
        });
    }

    /**
     * Validasi detail jurnal
     * 
     * Pastikan:
     * - Minimal 2 baris
     * - Setiap baris MINIMAL ada debit ATAU credit (tidak boleh keduanya 0)
     * - Tidak boleh ada debit DAN credit sekaligus di satu baris
     * - Total debit = Total credit (balance)
     */
    protected function validateJournalDetails(array $details): void
    {
        // Minimal 2 baris
        if (count($details) < 2) {
            throw new \Exception('Jurnal minimal harus memiliki 2 baris detail');
        }

        // Hitung total
        $totalDebit = 0;
        $totalCredit = 0;
        $data_index = 0;

        foreach ($details as $index => $detail) {
            $debit = (float)($detail['debit'] ?? 0);
            $credit = (float)($detail['credit'] ?? 0);

            $data_index++; 

            // VALIDASI KRITIS: Tidak boleh keduanya 0
            if ($debit == 0 && $credit == 0) {
                throw new \Exception(
                    "Baris detail #{$data_index} tidak valid: debit dan credit tidak boleh keduanya 0"
                );
            }

            // Validasi COA ada
            if (empty($detail['coa_id'])) {
                throw new \Exception("Baris detail #{$data_index}: coa_id tidak boleh kosong");
            }

            // Validasi COA exist
            if (!ChartOfAccount::find($detail['coa_id'])) {
                throw new \Exception("Baris detail #{$data_index}: COA ID {$detail['coa_id']} tidak ditemukan");
            }

            // Hitung total
            $totalDebit += $debit;
            $totalCredit += $credit;

            // Validasi: debit atau credit, tidak boleh keduanya isi
            if ($debit > 0 && $credit > 0) {
                throw new \Exception(
                    "Baris detail #{$data_index}: Satu baris hanya boleh ada debit ATAU credit, tidak keduanya"
                );
            }
        }

        // Validasi balance
        if (abs($totalDebit - $totalCredit) > 0.01) { // Toleransi floating point
            throw new \Exception(
                "Jurnal tidak balance! Debit: {$totalDebit}, Credit: {$totalCredit}"
            );
        }
    }

    /**
     * ===== JURNAL PENJUALAN =====
     * 
     * Jurnal:
     * - Debit Piutang Usaha = Total penjualan
     * - Kredit Penjualan = Total penjualan
     */
    public function createSalesJournal($transaction): Accounting
    {
        $total = $transaction->total ?? 0;

        return $this->createJournal('pos', $transaction->id, [
            [
                'coa_id' => $this->getCoaIdByCode('1-1200'),
                'debit' => $total,
                'credit' => 0,
                'description' => 'Piutang dari penjualan #' . $transaction->invoice_number,
            ],
            [
                'coa_id' => $this->getCoaIdByCode('4-4000'),
                'debit' => 0,
                'credit' => $total,
                'description' => 'Pendapatan penjualan #' . $transaction->invoice_number,
            ],
        ]);
    }

    /**
     * ===== JURNAL PEMBAYARAN PENJUALAN (PARTIAL / FULL) =====
     * 
     * Jurnal:
     * - Debit Kas/Bank = Jumlah dibayar
     * - Kredit Piutang Usaha = Jumlah dibayar
     * 
     * Menangani:
     * - Partial payment (sebagian dari total penjualan)
     * - Full payment (lunas)
     * 
     * @param Transaction $transaction
     * @param float $amountPaid Jumlah yang dibayarkan
     * @param string $bankAccountCode COA code untuk akun bank/kas (default '1-1000')
     * @return Accounting
     */
    public function createSalesPaymentJournal($transaction, float $amountPaid, string $bankAccountCode = '1-1000'): Accounting
    {
        // Validasi minimal ada pembayaran
        if ($amountPaid <= 0) {
            throw new \Exception('Jumlah pembayaran harus lebih dari 0');
        }

        // Validasi pembayaran tidak melebihi total
        $total = $transaction->total ?? 0;
        if ($amountPaid > $total) {
            throw new \Exception(
                "Pembayaran ({$amountPaid}) tidak boleh melebihi total transaksi ({$total})"
            );
        }

        return $this->createJournal('pos_payment', $transaction->id, [
            [
                'coa_id' => $this->getCoaIdByCode($bankAccountCode),
                'debit' => $amountPaid,
                'credit' => 0,
                'description' => 'Penerimaan pembayaran penjualan #' . $transaction->invoice_number,
            ],
            [
                'coa_id' => $this->getCoaIdByCode('1-1200'),
                'debit' => 0,
                'credit' => $amountPaid,
                'description' => 'Pelunasan piutang penjualan #' . $transaction->invoice_number,
            ],
        ]);
    }

    /**
     * ===== JURNAL PEMBELIAN STOK =====
     * 
     * Jurnal:
     * - Debit Persediaan = Total pembelian
     * - Kredit Hutang Usaha (jika belum dibayar) / Kas (jika dibayar)
     */
    public function createPurchaseJournal($warehouseLog): Accounting
    {
        $total = $warehouseLog->total ?? 0;

        if ($total <= 0) {
            throw new \Exception('Total pembelian harus lebih besar dari 0');
        }

        // Jurnal pembelian stok:
        // Debit Persediaan
        // Kredit Hutang Usaha
        return $this->createJournal('warehouse', $warehouseLog->id, [
            [
                'coa_id' => $this->getCoaIdByCode('1-1300'), // Persediaan
                'debit' => $total,
                'credit' => 0,
                'description' => 'Pembelian persediaan #' . ($warehouseLog->transaction_code ?? $warehouseLog->id),
            ],
            [
                'coa_id' => $this->getCoaIdByCode('2-2000'), // Hutang Usaha
                'debit' => 0,
                'credit' => $total,
                'description' => 'Hutang pembelian #' . ($warehouseLog->transaction_code ?? $warehouseLog->id),
            ],
        ]);
    }

    /**
     * ===== JURNAL PEMBAYARAN HUTANG =====
     * 
     * Jurnal:
     * - Debit Hutang Usaha = Jumlah dibayar
     * - Kredit Kas/Bank = Jumlah dibayar
     */
    public function createPurchasePaymentJournal($warehouseLog, float $amountPaid, string $cashAccountCode = '1-1000'): Accounting
    {
        if ($amountPaid <= 0) {
            throw new \Exception('Jumlah pembayaran harus lebih dari 0');
        }

        $total = $warehouseLog->total ?? 0;
        if ($amountPaid > $total) {
            throw new \Exception('Jumlah pembayaran tidak boleh melebihi total pembelian');
        }

        return $this->createJournal('warehouse_payment', $warehouseLog->id, [
            [
                'coa_id' => $this->getCoaIdByCode('2-2000'), // Hutang Usaha
                'debit' => $amountPaid,
                'credit' => 0,
                'description' => 'Pelunasan hutang pembelian #' . ($warehouseLog->transaction_code ?? $warehouseLog->id),
            ],
            [
                'coa_id' => $this->getCoaIdByCode($cashAccountCode), // Kas/Bank
                'debit' => 0,
                'credit' => $amountPaid,
                'description' => 'Kas keluar untuk pelunasan #' . ($warehouseLog->transaction_code ?? $warehouseLog->id),
            ],
        ]);
    }

    /**
     * ===== JURNAL PEMASUKAN KAS (INCOME) MENU PEMASUKAN/PENGELUARAN =====
     * 
     * Jurnal:
     * - Debit Kas = Jumlah masuk
     * - Kredit Pendapatan = Jumlah masuk
     */
    public function createCashInJournal($cashFlow, string $cashAccountCode = '1-1000'): Accounting
    {
        $amount = $cashFlow->amount ?? 0;

        return $this->createJournal('cashflow_income', $cashFlow->id, [
            [
                'coa_id' => $this->getCoaIdByCode($cashAccountCode),
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Pemasukan kas: ' . $cashFlow->description,
            ],
            [
                'coa_id' => $this->getCoaIdByCode('4-4100'),
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Pendapatan lain-lain: ' . $cashFlow->description,
            ],
        ]);
    }

    /**
     * ===== JURNAL PENGELUARAN KAS (EXPENSE) =====
     * 
     * Jurnal:
     * - Debit Beban = Jumlah keluar
     * - Kredit Kas = Jumlah keluar
     */
    public function createCashOutJournal($cashFlow, string $cashAccountCode = '1-1000'): Accounting
    {
        $amount = $cashFlow->amount ?? 0;

        return $this->createJournal('cashflow_expense', $cashFlow->id, [
            [
                'coa_id' => $this->getCoaIdByCode('5-5000'),
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Beban operasional: ' . $cashFlow->description,
            ],
            [
                'coa_id' => $this->getCoaIdByCode($cashAccountCode),
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Pengeluaran kas: ' . $cashFlow->description,
            ],
        ]);
    }

    /**
     * ===== JURNAL HPP (Harga Pokok Penjualan) =====
     * 
     * Jurnal:
     * - Debit HPP = Cost of Goods Sold
     * - Kredit Persediaan = Cost of Goods Sold
     * 
     * Catatan:
     * - HPP dihitung dari transaction_items.quantity * cost_price
     * - Atau bisa gunakan total_cost jika sudah tersimpan
     * - Jika items kosong atau HPP <= 0, skip (tidak perlu jurnal)
     */
    public function createHppJournal($transaction): ?Accounting
    {
        // Ambil items dari transaksi
        $items = $transaction->items ?? [];

        if (empty($items)) {
            Log::info("Transaksi #{$transaction->invoice_number} tidak punya items, skip HPP journal");
            return null;
        }

        // Hitung total HPP dari transaction_items
        // Gunakan total_cost jika ada, atau hitung dari quantity * cost_price
        $hppTotal = 0;
        foreach ($items as $item) {
            if (!empty($item->total_cost)) {
                $hppTotal += $item->total_cost;
            } else {
                $hppTotal += ($item->quantity ?? 0) * ($item->cost_price ?? 0);
            }
        }

        // Jika HPP = 0, tidak perlu jurnal
        if ($hppTotal <= 0) {
            Log::info("Transaksi #{$transaction->invoice_number} HPP = 0, skip HPP journal");
            return null;
        }

        return $this->createJournal('pos_hpp', $transaction->id, [
            [
                'coa_id' => $this->getCoaIdByCode('5-5300'),
                'debit' => $hppTotal,
                'credit' => 0,
                'description' => 'HPP dari penjualan #' . $transaction->invoice_number,
            ],
            [
                'coa_id' => $this->getCoaIdByCode('1-1300'),
                'debit' => 0,
                'credit' => $hppTotal,
                'description' => 'Pengurangan persediaan #' . $transaction->invoice_number,
            ],
        ]);
    }

    /**
     * ===== JURNAL REVERSAL (VOID) =====
     * 
     * Buat jurnal reversal, jangan menghapus jurnal asli
     * 
     * Jurnal reversal adalah pembalikan jurnal asli:
     * - Debit di jurnal asli menjadi Kredit di reversal
     * - Kredit di jurnal asli menjadi Debit di reversal
     */
    public function createReversalJournal(Accounting $originalAccounting, string $reason = ''): Accounting
    {
        return DB::transaction(function () use ($originalAccounting, $reason) {
            // Ambil detail dari jurnal asli
            $originalDetails = $originalAccounting->details()
                ->select('coa_id', 'debit', 'credit', 'description')
                ->get();

            // Balik debit & credit
            $reasonText = $reason ? " - {$reason}" : '';

            $reversalDetails = $originalDetails->map(function ($detail) use ($reasonText) {
                return [
                    'coa_id' => $detail->coa_id,
                    'debit' => $detail->credit,
                    'credit' => $detail->debit,
                    'description' => '[Reversal] ' . ($detail->description ?? '') . $reasonText,
                ];
            })->toArray();

            // Generate nomor jurnal baru
            $journalNumber = $this->generateJournalNumber();

            // Buat jurnal reversal dengan reference yang sama tapi is_reversal = true
            $reversalAccounting = Accounting::create([
                'journal_number' => $journalNumber,
                'reference_type' => $originalAccounting->reference_type,
                'reference_id' => $originalAccounting->reference_id,
                'total_debit' => $originalAccounting->total_credit,
                'total_credit' => $originalAccounting->total_debit,
                'status_accounting' => 'posting',
                'is_reversal' => true,
                'reversal_of' => $originalAccounting->id,
                'created_by' => auth()->check() ? auth()->id() : 1,
                'created_date' => now(),
            ]);

            // Insert detail reversal
            foreach ($reversalDetails as $detail) {
                AccountingDetail::create([
                    'accounting_id' => $reversalAccounting->id,
                    'coa_id' => $detail['coa_id'],
                    'debit' => $detail['debit'],
                    'credit' => $detail['credit'],
                    'description' => $detail['description'],
                    'created_by' => auth()->check() ? auth()->id() : 1,
                    'created_date' => now(),
                ]);
            }

            Log::info(
                "Jurnal reversal {$journalNumber} dibuat untuk membatalkan {$originalAccounting->journal_number}"
            );

            return $reversalAccounting;
        });
    }

    /**
     * ===== AMBIL JURNAL BERDASARKAN REFERENCE =====
     * 
     * Utility untuk mencari jurnal yang sudah dibuat
     */
    public function getJournalByReference(string $referenceType, int $referenceId): ?Accounting
    {
        return Accounting::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('is_reversal', false)
            ->first();
    }

    /**
     * ===== AMBIL SEMUA JURNAL UNTUK SUATU TRANSAKSI =====
     * 
     * Termasuk jurnal reversal
     */
    public function getJournalsByReference(string $referenceType, int $referenceId): \Illuminate\Database\Eloquent\Collection
    {
        return Accounting::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->orderBy('created_date')
            ->get();
    }
}
