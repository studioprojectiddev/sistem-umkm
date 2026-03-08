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
                $paid = $item->amount_paid ?? $item->paid ?? 0;
                $remaining = max(0, $item->total - $paid);
                $statusLabel = $remaining > 0 ? 'Belum Lunas' : 'Lunas';
                $transactionDate = $item->transaction_date ?? $item->created_at;
            @endphp
            <tr>
                <td>{{ $transactionDate ? \Carbon\Carbon::parse($transactionDate)->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->id }}</td>
                <td>{{ $item->supplier_name ?: '-' }}</td>
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
            <td colspan="6" style="font-weight:bold; text-align:right;">Total Hutang</td>
            <td style="font-weight:bold;">{{ $totalHutang }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
