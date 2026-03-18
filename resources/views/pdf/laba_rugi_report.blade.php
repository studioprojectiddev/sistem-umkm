<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { margin-bottom: 12px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header .meta { margin-top: 6px; font-size: 11px; color: #555; }
        .table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .table th, .table td { border: 1px solid #ccc; padding: 6px 8px; }
        .table th { background: #f4f4f4; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Laba Rugi</h1>
        <div class="meta">Periode: {{ $start ?: '-' }} s/d {{ $end ?: '-' }}</div>
    </div>
    <table class="table">
        <thead>
            <tr><th>Komponen</th><th class="text-right">Nilai</th></tr>
        </thead>
        <tbody>
            <tr><td>Pendapatan Penjualan</td><td class="text-right">Rp {{ number_format($penjualan,0,',','.') }}</td></tr>
            <tr><td>Pendapatan Lain</td><td class="text-right">Rp {{ number_format($pendapatanLain,0,',','.') }}</td></tr>
            <tr><td><strong>Total Pendapatan</strong></td><td class="text-right"><strong>Rp {{ number_format($penjualan+$pendapatanLain,0,',','.') }}</strong></td></tr>
            <tr><td>HPP</td><td class="text-right">Rp {{ number_format($hpp,0,',','.') }}</td></tr>
            <tr><td><strong>Laba Kotor</strong></td><td class="text-right"><strong>Rp {{ number_format($labaKotor,0,',','.') }}</strong></td></tr>
            <tr><td>Beban Operasional</td><td class="text-right">Rp {{ number_format($bebanOperasional,0,',','.') }}</td></tr>
            <tr><td>Beban Lain</td><td class="text-right">Rp {{ number_format($bebanLain,0,',','.') }}</td></tr>
            <tr><td><strong>Laba Bersih</strong></td><td class="text-right"><strong>Rp {{ number_format($labaBersih,0,',','.') }}</strong></td></tr>
        </tbody>
    </table>
</body>
</html>