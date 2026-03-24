@extends('layouts.app')

@section('title', 'Penjualan Dan Pelanggan')

@section('content')

<h1 class="title">📊 Penjualan & Pelanggan</h1>

<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="#" class="active">Penjualan Dan Pelanggan</a></li>
</ul>

<style>

/* ================= GLOBAL ================= */
body {
    background: #f5f7fb;
}

/* ================= MAIN GRID ================= */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr; /* kiri besar, kanan kecil */
    gap: 20px;
    margin-top: 20px;
}

/* ================= LEFT SIDE ================= */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* jadi 2x2 */
    gap: 15px;
}

/* ================= CARD ================= */
.summary-card {
    background: white;
    padding: 20px;
    border-radius: 14px;
    border: 1px solid #eee;
    box-shadow: 0 6px 18px rgba(0,0,0,0.05);
    transition: 0.3s;
}

.summary-card:hover {
    transform: translateY(-4px);
}

.summary-card .label {
    font-size: 13px;
    color: #888;
}

.summary-card .value {
    font-size: 24px;
    font-weight: bold;
    margin-top: 6px;
}

.green { color: #16a34a; }
.red { color: #dc2626; }

/* ================= RIGHT SIDE ================= */
.right-panel {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

/* ================= CARD ================= */
.card {
    background: white;
    border-radius: 14px;
    padding: 15px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.05);
}

.card h3 {
    margin-bottom: 10px;
}

/* ================= TABLE ================= */
.table-scroll {
    max-height: 260px;
    overflow-y: auto;
}

.custom-table {
    width: 100%;
    border-collapse: collapse;
}

.custom-table td {
    padding: 10px;
    font-size: 13px;
    border-bottom: 1px solid #eee;
}

.custom-table tr:hover {
    background: #f5f7ff;
}

.text-right {
    text-align: right;
}

/* ================= RANK ================= */
.rank {
    background: #eef2ff;
    color: #4f46e5;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 12px;
    margin-right: 6px;
}

/* ================= INSIGHT ================= */
.insight-box p {
    background: #f9fafb;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    font-size: 13px;
}

/* ================= RESPONSIVE ================= */
@media(max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .summary-grid {
        grid-template-columns: 1fr;
    }
}

.growth {
    font-size: 12px;
    margin-top: 6px;
    font-weight: 600;
}

.growth.up {
    color: #16a34a;
}

.growth.down {
    color: #dc2626;
}

.filter-bar {
    display: flex;
    align-items: end;
    gap: 10px;
    margin: 10px 0 20px;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-size: 12px;
    color: #666;
    margin-bottom: 3px;
}

.filter-group input {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #ddd;
    font-size: 13px;
}

/* button */
.btn-filter {
    background: #6c63ff;
    color: white;
    border: none;
    padding: 7px 14px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
}

.btn-filter:hover {
    background: #574fd6;
}

</style>

{{-- ================= FILTER TANGGAL ================= --}}
<form method="GET" style="margin-top:15px; display:flex; gap:10px; align-items:center;">
    
    <div class="filter-bar">

    <div class="filter-group">
        <label>Dari</label>
        <input type="date" id="startDate" name="start_date">
    </div>

    <div class="filter-group">
        <label>Sampai</label>
        <input type="date" id="endDate" name="end_date">
    </div>

    <button class="btn-filter" onclick="applyFilter()">
        🔍 Filter
    </button>

</div>

</form>


<div class="dashboard-grid">

    <!-- ================= LEFT (4 CARD) ================= -->
    <div class="summary-grid">

        <div class="summary-card">
            <div class="label">💰 Total Penjualan</div>
            <div class="value green">
                Rp {{ number_format($totalPenjualan) }}
            </div>
            <div class="growth {{ $growthPenjualan >= 0 ? 'up' : 'down' }}">
                {{ $growthPenjualan >= 0 ? '▲' : '▼' }}
                {{ number_format(abs($growthPenjualan),1) }}%
            </div>
        </div>

        <div class="summary-card">
            <div class="label">📊 Total Transaksi</div>
            <div class="value">
                {{ $totalTransaksi }}
            </div>
            <div class="growth {{ $growthTransaksi >= 0 ? 'up' : 'down' }}">
                {{ $growthTransaksi >= 0 ? '▲' : '▼' }}
                {{ number_format(abs($growthTransaksi),1) }}%
            </div>
        </div>

        <div class="summary-card">
            <div class="label">👥 Total Pelanggan Melakukan utang</div>
            <div class="value">
                {{ $totalPelanggan }}
            </div>
            <div class="growth {{ $growthPelanggan >= 0 ? 'up' : 'down' }}">
                {{ $growthPelanggan >= 0 ? '▲' : '▼' }}
                {{ number_format(abs($growthPelanggan),1) }}%
            </div>
        </div>

        <div class="summary-card">
            <div class="label">📉 Total Piutang</div>
            <div class="value red">
                Rp {{ number_format(abs($totalPiutang)) }}
            </div>
            <div class="growth {{ $growthPiutang >= 0 ? 'up' : 'down' }}">
                {{ $growthPiutang >= 0 ? '▲' : '▼' }}
                {{ number_format(abs($growthPiutang),1) }}%
            </div>
        </div>

    </div>

    <!-- ================= RIGHT SIDE ================= -->
    <div class="right-panel">

        <!-- 🔥 GANTI NAMA -->
        <div class="card">
            <h3>⚠️ Pelanggan Dengan Piutang Terbesar</h3>

            <div class="table-scroll">
                <table class="custom-table">
                    <tbody>
                        @forelse($topCustomers as $i => $c)
                        <tr style="{{ $i == 0 ? 'background:#fef3c7;font-weight:600;' : '' }}">
                            <td>
                                <span class="rank">#{{ $i+1 }}</span>
                                {{ $c->customer_name }}
                            </td>
                            <td class="text-right">
                                Rp {{ number_format($c->total_belanja) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- INSIGHT -->
        <div class="card">
            <h3>💡 Insight</h3>

            <div class="insight-box">

                <p>📊 Total transaksi: <b>{{ $totalTransaksi }}</b></p>

                <p>👥 Total Pelanggan Melakukan utang: <b>{{ $totalPelanggan }}</b></p>

                @if($totalPiutang > 0)
                    <p style="color:red;">
                        ⚠️ Ada piutang Rp {{ number_format($totalPiutang) }}
                    </p>
                @else
                    <p style="color:green;">
                        ✔ Semua pembayaran lunas
                    </p>
                @endif

                <p>
                    💡 Rekomendasi: Fokus follow up pelanggan dengan piutang terbesar terlebih dahulu.
                </p>

            </div>
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let today = new Date();

    // 🔥 tanggal hari ini
    let end = today.toISOString().split('T')[0];

    // 🔥 tanggal awal bulan
    let start = new Date(today.getFullYear(), today.getMonth(), 1)
        .toISOString().split('T')[0];

    document.getElementById('startDate').value = start;
    document.getElementById('endDate').value = end;

});
</script>

<script>
function applyFilter() {
    let start = document.getElementById('startDate').value;
    let end = document.getElementById('endDate').value;

    let url = new URL(window.location.href);

    url.searchParams.set('start_date', start);
    url.searchParams.set('end_date', end);

    window.location.href = url.toString();
}
</script>

@endsection