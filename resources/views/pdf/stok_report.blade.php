<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok</title>
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
        <h1>Laporan Stok</h1>
        <div class="meta">
            @if($start)
                Periode: {{ $start }}@if($end) s/d {{ $end }}@endif<br>
            @endif
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Variasi</th>
                <th class="text-right">Stok Awal</th>
                <th class="text-right">Stok Masuk</th>
                <th class="text-right">Stok Keluar</th>
                <th class="text-right">Saldo Akhir</th>
                <th class="text-right">HPP</th>
                <th class="text-right">Nilai Stok</th>
                <th class="text-right">Potensi Laba</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ optional($item->created_at) ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->product?->name ?? '-' }}</td>
                    <td>{{ $item->variation?->name ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item->stok_awal ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->stok_masuk ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->stok_keluar ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->saldo ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->hpp ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->nilai_stok ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->potensi_laba ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
