@php
    function formatRp($value) {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Neraca</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        h3 { text-align: center; margin-bottom: 20px; }
        .periode { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h3>Laporan Neraca</h3>
    <p class="periode">Periode: {{ $start ?: '-' }} s/d {{ $end ?: '-' }}</p>

    <table>
        <thead>
            <tr>
                <th>Nama Akun</th>
                <th class="text-right">Debet (Rp)</th>
                <th class="text-right">Kredit (Rp)</th>
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
                            <td class="text-right">{{ formatRp($item->debit) }}</td>
                            <td class="text-right">{{ formatRp($item->credit) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><strong>Subtotal {{ $group['label'] }}</strong></td>
                        <td class="text-right"><strong>{{ formatRp($group['subtotal_debit']) }}</strong></td>
                        <td class="text-right"><strong>{{ formatRp($group['subtotal_credit']) }}</strong></td>
                    </tr>
                @endforeach
            @endif

            @if(count($data['liabilities']) > 0)
                <tr>
                    <td colspan="3"><strong>LIABILITAS</strong></td>
                </tr>
                @foreach($data['liabilities'] as $group)
                    <tr>
                        <td colspan="3"><strong>{{ $group['label'] }}</strong></td>
                    </tr>
                    @foreach($group['items'] as $item)
                        <tr>
                            <td>{{ $item->code }} - {{ $item->name }}</td>
                            <td class="text-right">{{ formatRp($item->debit) }}</td>
                            <td class="text-right">{{ formatRp($item->credit) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><strong>Subtotal {{ $group['label'] }}</strong></td>
                        <td class="text-right"><strong>{{ formatRp($group['subtotal_debit']) }}</strong></td>
                        <td class="text-right"><strong>{{ formatRp($group['subtotal_credit']) }}</strong></td>
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
                            <td class="text-right">{{ formatRp($item->debit) }}</td>
                            <td class="text-right">{{ formatRp($item->credit) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><strong>Subtotal {{ $group['label'] }}</strong></td>
                        <td class="text-right"><strong>{{ formatRp($group['subtotal_debit']) }}</strong></td>
                        <td class="text-right"><strong>{{ formatRp($group['subtotal_credit']) }}</strong></td>
                    </tr>
                @endforeach
            @endif

            <tr>
                <td><strong>Total Debit = Total Kredit</strong></td>
                <td class="text-right"><strong>{{ formatRp($data['total_debit']) }}</strong></td>
                <td class="text-right"><strong>{{ formatRp($data['total_credit']) }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>