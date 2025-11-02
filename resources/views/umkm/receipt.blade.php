<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Transaksi</title>
    <style>
        /* Ukuran kertas thermal */
        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-family: 'Arial', 'DejaVu Sans', sans-serif;
            width: 80mm;
            margin: 0 auto; /* ✅ Biar struk berada di tengah halaman */
            padding: 5px;
            font-size: 12px;
            color: #000;
            background: #fff;
            text-align: center; /* ✅ Rata tengah secara default */
        }

        h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        img.logo {
            display: block;
            margin: 5px auto;
            width: 60px;
            height: auto;
        }

        hr {
            border: 0;
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        table {
            width: 95%;
            margin: 0 auto; /* ✅ Biar tabel tetap di tengah */
            border-collapse: collapse;
            text-align: left; /* ✅ Supaya teks item dan harga tetap sejajar */
        }

        table td {
            font-size: 12px;
            padding: 2px 0;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .total td {
            font-weight: bold;
            font-size: 13px;
            border-top: 1px dashed #000;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 11px;
        }

        /* Saat mode print */
        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                margin: 0 auto;
                padding: 0;
            }

            img {
                filter: contrast(150%) brightness(110%);
            }

            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>

<body onload="setTimeout(() => { window.print(); autoClose(); }, 300)">
    <h2>Imanuel</h2>
    <img src="{{ asset('assets/images/icon_imanuel2.png') }}" class="logo" alt="Logo Toko">
    <hr>

    <!-- Informasi Transaksi -->
    <p>No. Invoice: {{ $transaction->invoice_number }}</p>
    <p>Tanggal: {{ $transaction->created_at->format('d/m/Y H:i') }}</p>
    <p>Kasir: {{ auth()->user()->name ?? 'Admin' }}</p>
    <hr>

    <!-- Item Transaksi -->
    <table>
        @foreach($transaction->items as $item)
        <tr>
            <td style="width: 65%;">
                {{ $item->product->name }}
                @if($item->variation)
                    ({{ $item->variation->name }})
                @endif
                <br>
                {{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}
            </td>
            <td class="text-right" style="width: 35%;">
                Rp {{ number_format($item->quantity * $item->price, 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </table>

    <hr>

    <!-- Ringkasan -->
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
            <td>Metode</td>
            <td class="text-right">{{ ucfirst($transaction->payment_method ?? 'Cash') }}</td>
        </tr>
    </table>

    <hr>

    <!-- Footer -->
    <div class="footer">
        <p>Terima kasih atas kunjungan Anda!</p>
        <p>Follow & Support: {{ $storeContact ?? '085960296108' }}</p>
        <p>~ {{ config('app.name', 'StudioProjectID POS') }} ~</p>
    </div>

    <script>
        function autoClose() {
            // Tutup tab otomatis setelah 5 detik
            setTimeout(() => {
                window.close();
            }, 5000);
        }
    </script>
</body>
</html>