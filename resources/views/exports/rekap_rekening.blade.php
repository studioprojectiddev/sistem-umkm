<table>
    <thead>
        <tr>
            <th>Kode Rekening</th>
            <th>Nama Rekening</th>
            <th>Saldo Awal</th>
            <th>Total Debit</th>
            <th>Total Kredit</th>
            <th>Saldo Akhir</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->opening }}</td>
                <td>{{ $item->debit }}</td>
                <td>{{ $item->credit }}</td>
                <td>{{ $item->ending }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
