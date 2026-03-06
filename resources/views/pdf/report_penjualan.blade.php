<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { margin-bottom: 12px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header .meta { margin-top: 6px; font-size: 11px; color: #555; }
        .table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .table th, .table td { border: 1px solid #ccc; padding: 6px 8px; }
        .table th { background: #f4f4f4; }
        .text-right { text-align: right; }
        .small { font-size: 11px; color: #555; }
        .footer { margin-top: 14px; font-size: 12px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Penjualan</h1>
        <div class="meta">
            @if($start)
                Periode: {{ $start }}
                @if($end) s/d {{ $end }}@endif
                <br>
            @endif
            @if($warehouseId)
                Outlet: {{ optional(App\Models\Warehouse::find($warehouseId))->name ?? 'N/A' }}
                <br>
            @endif
            @if($status)
                Status: {{ ucfirst($status) }}
                <br>
            @endif
            Total Transaksi: {{ $items->count() }}
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kode Transaksi</th>
                <th>Pelanggan</th>
                <th>Produk</th>
                <th class="text-right">Jumlah</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ optional($item->transaction)->transaction_date ? \Carbon\Carbon::parse($item->transaction->transaction_date)->format('d/m/Y') : '-' }}</td>
                    <td>{{ optional($item->transaction)->invoice_number ?? '-' }}</td>
                    <td>{{ optional($item->transaction)->customer_name ?: 'Umum' }}</td>
                    <td>
                        {{ optional($item->product)->name ?? '-' }}
                        @if(optional($item->variation)->id)
                            ({{ $item->variation->name ?? 'Varian' }})
                        @endif
                    </td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="6" class="text-right" style="font-weight: bold;">Total</td>
                <td class="text-right" style="font-weight: bold;">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
