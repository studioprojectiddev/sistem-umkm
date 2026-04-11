@php
    function formatRp($value) {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
@endphp

<table>
    <thead>
        <tr>
            <th>Nama Akun</th>
            <th>Debit</th>
            <th>Kredit</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data['assets']) > 0)
            <tr>
                <td colspan="3"><strong>ASET</strong></td>
            </tr>
            @foreach($data['assets'] as $group)
                <tr>
                    <td colspan="3"><strong>{{ $group['label'] }}</strong></td>
                </tr>
                @foreach($group['items'] as $item)
                    <tr>
                        <td>{{ $item->code }} - {{ $item->name }}</td>
                        <td>{{ formatRp($item->debit) }}</td>
                        <td>{{ formatRp($item->credit) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Subtotal {{ $group['label'] }}</strong></td>
                    <td><strong>{{ formatRp($group['subtotal_debit']) }}</strong></td>
                    <td><strong>{{ formatRp($group['subtotal_credit']) }}</strong></td>
                </tr>
            @endforeach
        @endif

        @if(count($data['liabilities']) > 0)
            <tr>
                <td colspan="3"><strong>KEWAJIBAN</strong></td>
            </tr>
            @foreach($data['liabilities'] as $group)
                <tr>
                    <td colspan="3"><strong>{{ $group['label'] }}</strong></td>
                </tr>
                @foreach($group['items'] as $item)
                    <tr>
                        <td>{{ $item->code }} - {{ $item->name }}</td>
                        <td>{{ formatRp($item->debit) }}</td>
                        <td>{{ formatRp($item->credit) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Subtotal {{ $group['label'] }}</strong></td>
                    <td><strong>{{ formatRp($group['subtotal_debit']) }}</strong></td>
                    <td><strong>{{ formatRp($group['subtotal_credit']) }}</strong></td>
                </tr>
            @endforeach
        @endif

        @if(count($data['equities']) > 0)
            <tr>
                <td colspan="3"><strong>MODAL</strong></td>
            </tr>
            @foreach($data['equities'] as $group)
                <tr>
                    <td colspan="3"><strong>{{ $group['label'] }}</strong></td>
                </tr>
                @foreach($group['items'] as $item)
                    <tr>
                        <td>{{ $item->code }} - {{ $item->name }}</td>
                        <td>{{ formatRp($item->debit) }}</td>
                        <td>{{ formatRp($item->credit) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Subtotal {{ $group['label'] }}</strong></td>
                    <td><strong>{{ formatRp($group['subtotal_debit']) }}</strong></td>
                    <td><strong>{{ formatRp($group['subtotal_credit']) }}</strong></td>
                </tr>
            @endforeach
        @endif

        <tr>
            <td><strong>Total Debit = Total Kredit</strong></td>
            <td><strong>{{ formatRp($data['total_debit']) }}</strong></td>
            <td><strong>{{ formatRp($data['total_credit']) }}</strong></td>
        </tr>
    </tbody>
</table>