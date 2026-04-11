@extends('layouts.app')

@section('title', 'Dashboard Keuangan')

@section('content')

<style>
    .badge-income{
        background:#e6f9f2;
        color:#1cc88a;
        padding:4px 10px;
        border-radius:6px;
        font-size:0.8rem;
    }

    .badge-expense{
        background:#fdecea;
        color:#e74a3b;
        padding:4px 10px;
        border-radius:6px;
        font-size:0.8rem;
    }
</style>

        <div class="title">
            Dashboard Keuangan
        </div>

        <ul class="breadcrumbs">
            <li><a href="{{ route('dashboard') }}" class="active">Home</a></li>
            <li class="divider">/</li>
            <li>Dashboard Keuangan</li>
        </ul>

        <div class="card" style="margin-top: 24px; padding: 20px;">
            <form method="GET" action="{{ route('dashboard') }}" class="filters" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
                <div>
                    <label for="year">Tahun</label>
                    <select id="year" name="year" style="padding: 8px; border-radius: 8px; border: 1px solid #d1d5db;">
                        @foreach(range(now()->year - 3, now()->year + 1) as $optionYear)
                            <option value="{{ $optionYear }}" {{ $optionYear === $year ? 'selected' : '' }}>{{ $optionYear }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="month">Bulan</label>
                    <select id="month" name="month" style="padding: 8px; border-radius: 8px; border: 1px solid #d1d5db;">
                        <option value="">Semua Bulan</option>
                        @foreach($monthNames as $key => $label)
                            <option value="{{ $key }}" {{ $hasMonth && $key == $month ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="primary-button" style="padding: 10px 18px; background:#222F3F; color:#fff; border:none; border-radius:10px; cursor:pointer;">Terapkan Filter</button>
            </form>
        </div>

        <div class="info-data">
            <div class="card">
                <div class="head">
                    <div>
                        <h2>Kas & Bank</h2>
                        <p>Saldo tersedia</p>
                    </div>
                    <i class='bx bx-wallet icon'></i>
                </div>
                <p class="label" style="margin-top: 16px; font-size: 28px;">Rp {{ number_format($cashBankBalance, 0, ',', '.') }}</p>
            </div>

            <div class="card">
                <div class="head">
                    <div>
                        <h2>Total Pendapatan</h2>
                        <p>Periode terpilih</p>
                    </div>
                    <i class='bx bx-up-arrow-alt icon'></i>
                </div>
                <p class="label" style="margin-top: 16px; font-size: 28px;">Rp {{ number_format($incomeTotal, 0, ',', '.') }}</p>
            </div>

            <div class="card">
                <div class="head">
                    <div>
                        <h2>Total Pengeluaran</h2>
                        <p>Periode terpilih</p>
                    </div>
                    <i class='bx bx-down-arrow-alt icon down'></i>
                </div>
                <p class="label" style="margin-top: 16px; font-size: 28px;">Rp {{ number_format($expenseTotal, 0, ',', '.') }}</p>
            </div>

            <div class="card">
                <div class="head">
                    <div>
                        <h2>Laba Bersih</h2>
                        <p>Pendapatan - Pengeluaran</p>
                    </div>
                    <i class='bx bx-trending-up icon {{ $profitNet >= 0 ? '' : 'down' }}'></i>
                </div>
                <p class="label" style="margin-top: 16px; font-size: 28px; color: {{ $profitNet >= 0 ? '#2d9f69' : '#fc3b56' }};">Rp {{ number_format($profitNet, 0, ',', '.') }}</p>
            </div>

            <div class="card">
                <div class="head">
                    <div>
                        <h2>Piutang Usaha</h2>
                        <p>Penjualan belum lunas</p>
                    </div>
                    <i class='bx bx-user icon'></i>
                </div>
                <p class="label" style="margin-top: 16px;">Rp {{ number_format($receivableTotal, 0, ',', '.') }}</p>
            </div>

            <div class="card">
                <div class="head">
                    <div>
                        <h2>Hutang Usaha</h2>
                        <p>Pembelian belum dibayar</p>
                    </div>
                    <i class='bx bx-briefcase icon down'></i>
                </div>
                <p class="label" style="margin-top: 16px;">Rp {{ number_format($payableTotal, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="data">
            <div class="content-data" style="min-width: 500px;">
                <div class="head">
                    <h3>Pendapatan vs Pengeluaran</h3>
                </div>
                <div class="chart">
                    <div id="incomeExpenseChart" style="min-height: 360px;"></div>
                </div>
            </div>

            <div class="content-data" style="min-width: 320px;">
                <div class="head">
                    <h3>Distribusi Pengeluaran</h3>
                </div>
                <div class="chart">
                    <div id="expenseDonut" style="min-height: 360px;"></div>
                </div>
                <div style="margin-top: 18px;">
                    @foreach($expenseCategoriesChart as $item)
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #f3f4f6;">
                            <span>{{ $item['category'] }}</span>
                            <span>{{ number_format($item['total'], 0, ',', '.') }} ({{ $item['percent'] }}%)</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="data" style="margin-top: 20px;">
            <div class="content-data" style="flex: 1 1 600px;">
                <div class="head">
                    <h3>Ringkasan Arus Kas</h3>
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-top:16px;">
                    <div class="card" style="padding:16px;">
                        <p class="label">Kas Masuk</p>
                        <p style="font-size: 24px; margin-top: 10px; color: #2d9f69;">Rp {{ number_format($cashIn, 0, ',', '.') }}</p>
                    </div>
                    <div class="card" style="padding:16px;">
                        <p class="label">Kas Keluar</p>
                        <p style="font-size: 24px; margin-top: 10px; color: #fc3b56;">Rp {{ number_format($cashOut, 0, ',', '.') }}</p>
                    </div>
                    <div class="card" style="padding:16px;">
                        <p class="label">Saldo Awal</p>
                        <p style="font-size: 24px; margin-top: 10px;">Rp {{ number_format($previousBalance, 0, ',', '.') }}</p>
                    </div>
                    <div class="card" style="padding:16px;">
                        <p class="label">Saldo Akhir</p>
                        <p style="font-size: 24px; margin-top: 10px;">Rp {{ number_format($endingBalance, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="content-data" style="flex: 1 1 320px;">
                <div class="head">
                    <h3>Filter Periode</h3>
                </div>
                <p>Menampilkan data untuk:</p>
                <ul style="margin-top:12px; list-style:none; padding:0;">
                    <li><strong>Tahun:</strong> {{ $year }}</li>
                    <li><strong>Bulan:</strong> {{ $hasMonth ? $monthNames[$month] : 'Semua Bulan' }}</li>
                </ul>
            </div>
        </div>

        <div class="data" style="margin-top: 20px; gap: 20px; flex-wrap: wrap;">
            <div class="content-data" style="flex: 1 1 100%;">
                <div class="head">
                    <h3>Transaksi Terakhir</h3>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom:1px solid #e5e7eb; text-align:left;">
                                <th style="padding:12px 8px;">Tanggal</th>
                                <th style="padding:12px 8px;">Keterangan</th>
                                <th style="padding:12px 8px;">Tipe</th>
                                <th style="padding:12px 8px;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCashFlows as $flow)
                                <tr>
                                    <td style="padding:12px 8px;">{{ 
                                        \Carbon\Carbon::parse($flow->transaction_date)->format('d M Y') }}</td>
                                    <td style="padding:12px 8px;">{{ $flow->description ?: $flow->reference ?: 'Tidak ada keterangan' }}</td>
                                    <td style="padding:12px 8px; text-transform: capitalize;">
                                        @if($flow->type=='income')
                                            <span class="badge-income">Pemasukan</span>
                                        @else
                                            <span class="badge-expense">Pengeluaran</span>
                                        @endif    
                                    </td>
                                    <td style="padding:12px 8px;">Rp {{ number_format($flow->amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="padding:12px 8px; text-align:center;">Tidak ada transaksi terbaru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-data" style="flex: 1 1 48%; min-width: 300px;">
                <div class="head">
                    <h3>Piutang Terbesar</h3>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom:1px solid #e5e7eb; text-align:left;">
                                <th style="padding:12px 8px;">Pelanggan</th>
                                <th style="padding:12px 8px;">Tanggal</th>
                                <th style="padding:12px 8px;">Piutang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($receivables as $item)
                                <tr>
                                    <td style="padding:12px 8px;">{{ $item->customer_name ?: $item->invoice_number }}</td>
                                    <td style="padding:12px 8px;">{{ \Carbon\Carbon::parse($item->transaction_date)->format('d M Y') }}</td>
                                    <td style="padding:12px 8px;">Rp {{ number_format($item->receivable, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="padding:12px 8px; text-align:center;">Tidak ada piutang belum lunas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-data" style="flex: 1 1 48%; min-width: 300px;">
                <div class="head">
                    <h3>Hutang Terbesar</h3>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom:1px solid #e5e7eb; text-align:left;">
                                <th style="padding:12px 8px;">Supplier</th>
                                <th style="padding:12px 8px;">Tanggal</th>
                                <th style="padding:12px 8px;">Hutang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payables as $item)
                                <tr>
                                    <td style="padding:12px 8px;">{{ $item->supplier_name ?: 'Supplier tidak diketahui' }}</td>
                                    <td style="padding:12px 8px;">{{ $item->transaction_date ? \Carbon\Carbon::parse($item->transaction_date)->format('d M Y') : '-' }}</td>
                                    <td style="padding:12px 8px;">Rp {{ number_format($item->payable, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="padding:12px 8px; text-align:center;">Tidak ada hutang belum dibayar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const incomeExpenseChart = document.querySelector('#incomeExpenseChart');
            const expenseDonut = document.querySelector('#expenseDonut');

            if (incomeExpenseChart) {
                const options = {
                    series: [
                        { name: 'Pendapatan', data: @json($incomeByMonth) },
                        { name: 'Pengeluaran', data: @json($expenseByMonth) }
                    ],
                    chart: {
                        type: 'bar',
                        height: 360,
                        stacked: false,
                        toolbar: { show: false }
                    },
                    dataLabels: { enabled: false },
                    plotOptions: { bar: { columnWidth: '100%' } },
                    stroke: { show: true, width: 2, colors: ['transparent'] },
                    xaxis: { categories: @json($monthLabels) },
                    yaxis: {
                        labels: {
                            formatter: function (value) {
                                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
                        y: {
                            formatter: function (value) {
                                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
                            }
                        }
                    }
                };
                const chart = new ApexCharts(incomeExpenseChart, options);
                chart.render();
            }

            if (expenseDonut) {
                const chart = new ApexCharts(expenseDonut, {
                    series: @json($expenseCategoriesChart->pluck('total')),
                    chart: { type: 'donut', height: 360 },
                    labels: @json($expenseCategoriesChart->pluck('category')),
                    dataLabels: { enabled: false },
                    legend: { position: 'bottom' },
                    tooltip: {
                        enabled: true,
                        y: {
                            formatter: function (value) {
                                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
                            }
                        }
                    }
                });
                chart.render();
            }
        });
    </script>
@endsection