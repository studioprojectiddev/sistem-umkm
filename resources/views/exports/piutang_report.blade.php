<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>No Transaksi</th>
            <th>Pelanggan</th>
            <th>Tempo</th>
            <th>Total Penjualan</th>
            <th>Sudah Dibayar</th>
            <th>Sisa Piutang</th>
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
                <td>{{ $item->total }}</td>
                <td>{{ $paid }}</td>
                <td>{{ $remaining }}</td>
                <td>{{ $statusLabel }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" style="font-weight:bold; text-align:right;">Total Piutang</td>
            <td style="font-weight:bold;">{{ $totalPiutang }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
