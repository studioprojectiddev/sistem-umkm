<table>
    <thead>
        <tr>
            <th>Bagian</th>
            <th>Deskripsi</th>
            <th>Kas Masuk</th>
            <th>Kas Keluar</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report['categories'] as $category)
            <tr>
                <td colspan="4"><strong>{{ $category['label'] }}</strong></td>
            </tr>
            @if($category['items']->isEmpty())
                <tr>
                    <td></td>
                    <td>Tidak ada data</td>
                    <td>0</td>
                    <td>0</td>
                </tr>
            @endif
            @foreach($category['items'] as $item)
                <tr>
                    <td></td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->income ? $item->income : 0 }}</td>
                    <td>{{ $item->expense ? $item->expense : 0 }}</td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td><strong>Total {{ $category['label'] }}</strong></td>
                <td><strong>{{ $category['income'] }}</strong></td>
                <td><strong>{{ $category['expense'] }}</strong></td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="2"><strong>Total Kas Masuk</strong></td>
            <td colspan="2"><strong>{{ $report['total_income'] }}</strong></td>
        </tr>
        <tr>
            <td colspan="2"><strong>Total Kas Keluar</strong></td>
            <td colspan="2"><strong>{{ $report['total_expense'] }}</strong></td>
        </tr>
        <tr>
            <td colspan="2"><strong>Kenaikan / Penurunan Kas</strong></td>
            <td colspan="2"><strong>{{ $report['net_change'] }}</strong></td>
        </tr>
        <tr>
            <td colspan="2"><strong>Saldo Kas Awal</strong></td>
            <td colspan="2"><strong>{{ $report['opening_balance'] }}</strong></td>
        </tr>
        <tr>
            <td colspan="2"><strong>Saldo Kas Akhir</strong></td>
            <td colspan="2"><strong>{{ $report['ending_balance'] }}</strong></td>
        </tr>
    </tbody>
</table>
