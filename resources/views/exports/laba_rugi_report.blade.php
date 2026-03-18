@php
    function formatRp($value) {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
@endphp

<table>
    <thead>
        <tr>
            <th>Komponen</th>
            <th>Nilai</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>Pendapatan Penjualan</td><td>{{ formatRp($penjualan) }}</td></tr>
        <tr><td>Pendapatan Lain</td><td>{{ formatRp($pendapatanLain) }}</td></tr>
        <tr><td><strong>Total Pendapatan</strong></td><td><strong>{{ formatRp($penjualan + $pendapatanLain) }}</strong></td></tr>
        <tr><td>HPP</td><td>{{ formatRp($hpp) }}</td></tr>
        <tr><td><strong>Laba Kotor</strong></td><td><strong>{{ formatRp($labaKotor) }}</strong></td></tr>
        <tr><td>Beban Operasional</td><td>{{ formatRp($bebanOperasional) }}</td></tr>
        <tr><td>Beban Lain</td><td>{{ formatRp($bebanLain) }}</td></tr>
        <tr><td><strong>Laba Bersih</strong></td><td><strong>{{ formatRp($labaBersih) }}</strong></td></tr>
    </tbody>
</table>
