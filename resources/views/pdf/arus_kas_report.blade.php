@php
    function formatRp($value) {
        $formatted = 'Rp ' . number_format($value, 0, ',', '.');
        return $value < 0 ? '(' . str_replace('-Rp ', 'Rp ', $formatted) . ')' : $formatted;
    }
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Arus Kas</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        h3, h4 { text-align: left; margin-bottom: 10px; }
        .periode { margin-bottom: 20px; }
        .summary-table td { border: none; padding: 6px 8px; }
    </style>
</head>
<body>
    <h3>Laporan Arus Kas</h3>
    <p class="periode">Periode: {{ $report['start'] ?: '-' }} s/d {{ $report['end'] ?: '-' }}</p>

    @foreach($report['categories'] as $category)
        <h4>{{ $category['label'] }}</h4>
        <table>
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th class="text-right">Kas Masuk (IDR)</th>
                    <th class="text-right">Kas Keluar (IDR)</th>
                </tr>
            </thead>
            <tbody>
                @if($category['items']->isEmpty())
                    <tr>
                        <td>Tidak ada data</td>
                        <td class="text-right">Rp 0</td>
                        <td class="text-right">Rp 0</td>
                    </tr>
                @endif
                @foreach($category['items'] as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-right">{{ $item->income ? formatRp($item->income) : '—' }}</td>
                        <td class="text-right">{{ $item->expense ? formatRp($item->expense) : '—' }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Total {{ $category['label'] }}</strong></td>
                    <td class="text-right"><strong>{{ formatRp($category['income']) }}</strong></td>
                    <td class="text-right"><strong>{{ formatRp($category['expense']) }}</strong></td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <h4>Ringkasan</h4>
    <table class="summary-table">
        <tbody>
            <tr><td>Total Kas Masuk</td><td class="text-right">{{ formatRp($report['total_income']) }}</td></tr>
            <tr><td>Total Kas Keluar</td><td class="text-right">{{ formatRp($report['total_expense']) }}</td></tr>
            <tr><td>Kenaikan / Penurunan Kas</td><td class="text-right">{{ formatRp($report['net_change']) }}</td></tr>
            <tr><td>Saldo Kas Awal</td><td class="text-right">{{ formatRp($report['opening_balance']) }}</td></tr>
            <tr><td>Saldo Kas Akhir</td><td class="text-right">{{ formatRp($report['ending_balance']) }}</td></tr>
        </tbody>
    </table>
</body>
</html>
