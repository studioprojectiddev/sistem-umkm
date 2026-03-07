<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Rekening</title>
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
        <h1>Laporan Rekap Rekening</h1>
        <div class="meta">
            @if($start)
                Periode: {{ $start }}@if($end) s/d {{ $end }}@endif<br>
            @endif
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Kode Rekening</th>
                <th>Nama Rekening</th>
                <th class="text-right">Saldo Awal</th>
                <th class="text-right">Total Debit</th>
                <th class="text-right">Total Kredit</th>
                <th class="text-right">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-right">Rp {{ number_format($item->opening, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->debit, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->credit, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->ending, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
