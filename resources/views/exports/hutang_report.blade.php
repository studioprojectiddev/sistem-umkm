<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>No Transaksi</th>
            <th>Supplier</th>
            <th>Tempo</th>
            <th>Total Transaksi</th>
            <th>Sudah Dibayar</th>
            <th>Sisa Hutang</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            @php
                $statusLabel = $item->sisa_hutang > 0 ? 'Belum Lunas' : 'Lunas';
            @endphp
            <tr>
                <td>{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->transaction_code ?: $item->id }}</td>
                <td>{{ $item->supplier ?: '-' }}</td>
                <td>{{ $item->tempo ? \Carbon\Carbon::parse($item->tempo)->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->total }}</td>
                <td>{{ $item->sudah_dibayar }}</td>
                <td>{{ $item->sisa_hutang }}</td>
                <td>{{ $statusLabel }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" style="font-weight:bold; text-align:right;">Total Hutang</td>
            <td style="font-weight:bold;">{{ $totalHutang }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
