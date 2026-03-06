<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Piutang Penjualan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { margin-bottom: 12px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header .meta { margin-top: 6px; font-size: 11px; color: #555; }
        .table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .table th, .table td { border: 1px solid #ccc; padding: 6px 8px; }
        .table th { background: #f4f4f4; }
        .text-right { text-align: right; }
        .footer { margin-top: 14px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Piutang Penjualan</h1>
        <div class="meta">
            @if($start)
                Periode: {{ $start }}@if($end) s/d {{ $end }}@endif<br>
            @endif
            @if($customerName)
                Pelanggan: {{ $customerName }}<br>
            @endif
            @if($tempo)
                Tempo: {{ strtoupper(str_replace('_', ' ', $tempo)) }} @if($tempo === 'sdt' && $sdtDate) (s/d {{ $sdtDate }}) @endif<br>
            @endif
            @if($status)
                Status: {{ ucfirst($status) }}<br>
            @endif
            Total Piutang: Rp {{ number_format($totalPiutang,0,',','.') }}
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No Transaksi</th>
                <th>Pelanggan</th>
                <th>Tempo</th>
                <th class="text-right">Total Penjualan</th>
                <th class="text-right">Sudah Dibayar</th>
                <th class="text-right">Sisa Piutang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                @php
                    $paid = $item->uang_diterima ?? 0;
                    $remaining = max(0, $item->total - $paid);
                    $statusLabel = $remaining > 0 ? 'Belum Lunas' : 'Lunas';
                @endphp
                <tr>
                    <td>{{ optional($item->transaction_date) ? \Carbon\Carbon::parse($item->transaction_date)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->invoice_number }}</td>
                    <td>{{ $item->customer_name ?: 'Umum' }}</td>
                    <td>{{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('d/m/Y') : '-' }}</td>
                    <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($paid, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                    <td>{{ $statusLabel }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right" style="font-weight:bold;">Total Piutang</td>
                <td class="text-right" style="font-weight:bold;">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
