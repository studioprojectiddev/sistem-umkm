@extends('layouts.app')

@section('title', 'Analytic Products')

@section('content')
<h1 class="title">📊 Analytic Products</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.product.insight') }}" class="active">AI Insight Produk</a></li>
</ul>

<style>
/* === CARD & STAT STYLE === */
.analytics-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.2rem;
    margin-bottom: 2rem;
}
.stat-card {
    background: #fff;
    padding: 1.5rem;
    border-radius: 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    text-align: center;
    transition: all 0.25s ease;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}
.stat-title {
    font-size: 0.95rem;
    color: #777;
}
.stat-value {
    font-size: 1.6rem;
    font-weight: 700;
    color: #333;
    margin-top: 0.3rem;
}

/* === TABS STYLE === */
.tabs-container {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    overflow: hidden;
}
.tabs-nav {
    display: flex;
    border-bottom: 1px solid #eee;
    background: #f9fafc;
}
.tabs-nav button {
    flex: 1;
    padding: 1rem;
    border: none;
    background: none;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all 0.3s ease;
}
.tabs-nav button.active {
    color: #4e73df;
    border-bottom: 3px solid #4e73df;
    background: #fff;
}
.tab-content {
    display: none;
    padding: 1.5rem;
    animation: fadeIn 0.3s ease;
}
.tab-content.active {
    display: block;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

/* === TABLE STYLE === */
.analytics-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}
.analytics-table th, .analytics-table td {
    padding: 10px 12px;
    border: 1px solid #e2e2e2;
    text-align: left;
    font-size: 0.9rem;
}
.analytics-table th {
    background-color: #f5f7fb;
    font-weight: 600;
    color: #444;
}
.analytics-table tbody tr:hover {
    background-color: #f9fbff;
}
.danger-row {
    background: #ffe8e8;
}
.warning-row {
    background: #fff4e0;
}

/* === CHART STYLE === */
.chart-bar {
    height: 160px;
    display: flex;
    align-items: flex-end;
    gap: 6px;
}
.chart-bar div {
    flex: 1;
    background: #4e73df;
    border-radius: 5px 5px 0 0;
    transition: all 0.3s ease;
}
.chart-bar div:hover {
    background: #2e59d9;
}
.chart-bar-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    color: #666;
    margin-top: 0.5rem;
}
</style>

{{-- === RINGKASAN STATISTIK === --}}
<div class="analytics-container" style="margin-top:10px;">
    <div class="stat-card">
        <div class="stat-title">Total Produk</div>
        <div class="stat-value">{{ number_format($totalProducts) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Total Penjualan</div>
        <div class="stat-value">{{ number_format($totalSales) }} Unit</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Total Pendapatan</div>
        <div class="stat-value">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Produk Stok Rendah</div>
        <div class="stat-value">{{ $lowStockCount }}</div>
    </div>
</div>

{{-- === TAB NAVIGASI === --}}
<div class="tabs-container">
    <div class="tabs-nav">
        <button class="active" data-tab="tab-top">🔥 Terlaris</button>
        <button data-tab="tab-low">📉 Kurang Laku</button>
        <button data-tab="tab-stock">⚠️ Stok Rendah</button>
        <button data-tab="tab-trend">📆 Tren Penjualan</button>
    </div>

    {{-- === TAB: PRODUK TERLARIS === --}}
    <div id="tab-top" class="tab-content active">
        @if($topSelling->isEmpty())
            <p class="text-muted">Belum ada data penjualan.</p>
        @else
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Terjual</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topSelling as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->total_sold }}</td>
                            <td>Rp{{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- === TAB: PRODUK KURANG LARIS === --}}
    <div id="tab-low" class="tab-content">
        @if($lowSelling->isEmpty())
            <p class="text-muted">Semua produk masih aktif terjual.</p>
        @else
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Terjual</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowSelling as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->total_sold }}</td>
                            <td>Rp{{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- === TAB: PRODUK STOK RENDAH === --}}
    <div id="tab-stock" class="tab-content">
        @if($lowStockProducts->isEmpty())
            <p class="text-muted">Tidak ada produk dengan stok rendah.</p>
        @else
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Stok</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockProducts as $p)
                        <tr class="{{ $p->stock <= 3 ? 'danger-row' : 'warning-row' }}">
                            <td>{{ $p->full_name }}</td>
                            <td>{{ $p->stock }}</td>
                            <td>
                                @if($p->stock <= 3)
                                    <span class="badge badge-danger">Segera Restock</span>
                                @else
                                    <span class="badge badge-warning">Pantau Stok</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- === TAB: TREND PENJUALAN === --}}
    <div id="tab-trend" class="tab-content">
        @if($salesTrend->isEmpty())
            <p class="text-muted">Belum ada data penjualan 7 hari terakhir.</p>
        @else
            <div class="chart-bar">
                @php $max = $salesTrend->max('total_amount') ?: 1; @endphp
                @foreach($salesTrend as $day)
                    <div style="height: {{ ($day->total_amount / $max) * 100 }}%;"></div>
                @endforeach
            </div>
            <div class="chart-bar-labels">
                @foreach($salesTrend as $day)
                    <span>{{ \Carbon\Carbon::parse($day->date)->format('d M') }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.tabs-nav button');
    const tabs = document.querySelectorAll('.tab-content');

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('active'));
            tabs.forEach(tab => tab.classList.remove('active'));

            btn.classList.add('active');
            document.getElementById(btn.dataset.tab).classList.add('active');
        });
    });
});
</script>
@endsection