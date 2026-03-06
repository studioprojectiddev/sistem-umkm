<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Kode Transaksi</th>
            <th>Pelanggan</th>
            <th>Produk</th>
            <th>Jumlah</th>
            <th>Harga</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ optional($item->transaction)->transaction_date ? \Carbon\Carbon::parse($item->transaction->transaction_date)->format('d/m/Y') : '' }}</td>
                <td>{{ optional($item->transaction)->invoice_number }}</td>
                <td>{{ optional($item->transaction)->customer_name ?: 'Umum' }}</td>
                <td>
                    {{ optional($item->product)->name }}
                    @if(optional($item->variation)->id)
                        ({{ $item->variation->name }})
                    @endif
                </td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->price }}</td>
                <td>{{ $item->subtotal }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" style="text-align:right; font-weight:bold;">Total</td>
            <td style="font-weight:bold;">{{ $total }}</td>
        </tr>
    </tfoot>
</table>
