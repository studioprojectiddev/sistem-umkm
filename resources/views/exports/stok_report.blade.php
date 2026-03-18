<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Produk</th>
            <th>Variasi</th>
            <th>Stok Awal</th>
            <th>Stok Masuk</th>
            <th>Stok Keluar</th>
            <th>Saldo Akhir</th>
            <th>HPP</th>
            <th>Nilai Stok</th>
            <th>Potensi Laba</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ optional($item->created_at) ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->product?->name ?? '-' }}</td>
                <td>{{ $item->variation?->name ?? '-' }}</td>
                <td>{{ $item->stok_awal ?? 0 }}</td>
                <td>{{ $item->stok_masuk ?? 0 }}</td>
                <td>{{ $item->stok_keluar ?? 0 }}</td>
                <td>{{ $item->saldo ?? 0 }}</td>
                <td>{{ number_format($item->hpp ?? 0, 0, ',', '.') }}</td>
                <td>{{ number_format($item->nilai_stok ?? 0, 0, ',', '.') }}</td>
                <td>{{ number_format($item->potensi_laba ?? 0, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
