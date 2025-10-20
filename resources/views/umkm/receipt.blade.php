<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Transaksi</title>
    <style>
        /* General */
        body {
            font-family: 'Courier New', monospace;
            width: 80mm; /* standar printer thermal */
            margin: 0 auto;
            padding: 10px;
            color: #000;
        }

        h2, h3 {
            text-align: center;
            margin: 5px 0;
        }

        p, span {
            margin: 0;
            padding: 0;
        }

        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        /* Logo */
        .logo {
            display: block;
            margin: 0 auto 5px;
            width: 60px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        table td {
            padding: 3px 0;
        }

        .text-right {
            text-align: right;
        }

        .total {
            font-weight: bold;
            font-size: 1.1em;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 0.85em;
        }

        /* Highlight promo or payment */
        .payment-method {
            font-weight: bold;
            text-align: center;
            margin: 5px 0;
        }
    </style>
</head>
<body onload="window.print(); autoClose()">

    <h2>Imanuel</h2>
    <img src="{{ asset('assets/images/icon_imanuel2.png') }}" class="logo" alt="Logo Toko">
    <hr>

    <!-- Invoice Info -->
    <p>No. Invoice: {{ $transaction->invoice_number }}</p>
    <p>Tanggal: {{ $transaction->created_at->format('d/m/Y H:i') }}</p>
    <p>Kasir: {{ auth()->user()->name ?? 'Admin' }}</p>
    <hr>

    <!-- Items -->
    <table>
        @foreach($transaction->items as $item)
        <tr>
            <td>
                {{ $item->product->name }}
                @if($item->variation)
                    ({{ $item->variation->name }})
                @endif
            </td>
            <td class="text-right">
                {{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </table>
    <hr>

    <!-- Summary -->
    <table>
        <tr>
            <td>Subtotal</td>
            <td class="text-right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Diskon</td>
            <td class="text-right">Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pajak</td>
            <td class="text-right">Rp {{ number_format($transaction->tax, 0, ',', '.') }}</td>
        </tr>
        <tr class="total">
            <td>Total</td>
            <td class="text-right">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Bayar</td>
            <td class="text-right">{{ ucfirst($transaction->payment_method ?? 'Cash') }}</td>
        </tr>
    </table>
    <hr>

    <!-- Footer -->
    <div class="footer">
        <p>Terima kasih atas kunjungan Anda!</p>
        <p>Follow & Support: {{ $storeContact ?? '-' }}</p>
    </div>

    <script>
        function autoClose() {
            // Tunggu 5 detik (5000ms), lalu tutup jendela/tab
            setTimeout(() => {
                window.close();
            }, 5000);
        }
    </script>

</body>
</html>