<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Tipe</th>
            <th>Rekening</th>
            <th>Kategori</th>
            <th>Nominal</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cashflows as $c)
        <tr>
            <td>{{ $c->transaction_date }}</td>
            <td>{{ ucfirst($c->type) }}</td>
            <td>{{ $c->account->name ?? '-' }}</td>
            <td>{{ $c->category->name ?? '-' }}</td>
            <td>{{ $c->amount }}</td>
            <td>{{ $c->description }}</td>
        </tr>
        @endforeach
    </tbody>
</table>