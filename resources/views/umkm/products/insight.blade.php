@extends('layouts.app')

@section('title', 'AI Insight Produk')

@section('content')
<h1 class="title">AI Insight Produk</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.product.insight') }}" class="active">AI Insight Produk</a></li>
</ul>

<div class="page-wrapper" style="margin-top:10px;">
    @if($message)
        <div class="alert alert-info text-center mt-4">
            {{ $message }}
        </div>
    @else

        {{-- === TAB NAVIGASI === --}}
        <div class="tab-container">
            <button class="tab-btn active" data-tab="summary">📊 Ringkasan Insight</button>
            <button class="tab-btn" data-tab="top">🔥 Produk Terlaris</button>
            <button class="tab-btn" data-tab="low">⚠️ Stok Rendah</button>
            <button class="tab-btn" data-tab="price">💰 Saran Harga</button>
            <button class="tab-btn" data-tab="predict">📈 Prediksi Penjualan</button>
        </div>

        {{-- === TAB KONTEN 1: Ringkasan === --}}
        <div id="tab-summary" class="tab-content active">
            <!-- 🔍 Filter dan Search -->
            <div class="insight-controls">
                <input type="text" id="searchInsight" placeholder="Cari insight...">
                <select id="filterInsight">
                    <option value="all">Semua</option>
                    <option value="🔥 Produk Terlaris">🔥 Produk Terlaris</option>
                    <option value="⚠️ Stok Menipis">⚠️ Stok Menipis</option>
                    <option value="💰 Saran Harga">💰 Saran Harga</option>
                    <option value="📈 Prediksi Penjualan">📈 Prediksi Penjualan</option>
                </select>
            </div>

            <!-- 💡 Daftar Insight -->
            <div class="insight-grid" id="insightGrid">
                @foreach($insights as $index => $item)
                    <div class="insight-card" 
                        data-type="{{ $item['type'] }}" 
                        data-detail="{{ strtolower($item['detail']) }}"
                        data-index="{{ $index }}">
                        <div class="insight-type">{{ $item['type'] }}</div>
                        <div class="insight-detail">{{ $item['detail'] }}</div>
                    </div>
                @endforeach
            </div>

            <!-- 📄 Navigasi Prev / Next -->
            <div class="pagination-buttons">
                <button id="prevBtn" disabled>← Sebelumnya</button>
                <span id="pageInfo">Halaman 1</span>
                <button id="nextBtn">Berikutnya →</button>
            </div>
        </div>

        {{-- === TAB KONTEN 2: Produk Terlaris === --}}
        <div id="tab-top" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3>🔥 Produk Terlaris (Top 5)</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="insight-table">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Terjual</th>
                                <th>Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $p)
                                <tr>
                                    <td>{{ $p->final_name }}</td>
                                    <td>{{ $p->total_sold }} unit</td>
                                    <td>Rp{{ number_format($p->total_income, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- === TAB KONTEN 3: Stok Rendah === --}}
        <div id="tab-low" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3>⚠️ Produk dengan Stok Rendah</h3>
                </div>
                <div class="card-body table-responsive">
                    @if($lowStock->isEmpty())
                        <p class="text-muted">Tidak ada produk dengan stok rendah.</p>
                    @else
                        <table class="insight-table" id="lowStockTable">
                            <thead>
                                <tr>
                                    <th>Nama Produk</th>
                                    <th>Stok Tersisa</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="lowStockBody">
                                @foreach($lowStock as $p)
                                    <tr class="{{ $p->final_stock <= 3 ? 'danger-row' : 'warning-row' }}">
                                        <td>{{ $p->final_name }}</td>
                                        <td>{{ $p->final_stock }}</td>
                                        <td>
                                            @if($p->final_stock <= 3)
                                                <span class="badge badge-danger">Segera Restock</span>
                                            @else
                                                <span class="badge badge-warning">Pantau Stok</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- 🔄 Pagination --}}
                        <div class="pagination-buttons text-center mt-3" style="margin-top:10px;">
                            <button id="prevLowStock" class="btn btn-secondary btn-sm me-2">⬅ Prev</button>
                            <span id="pageInfoLowStock" class="mx-2 fw-bold"></span>
                            <button id="nextLowStock" class="btn btn-secondary btn-sm">Next ➡</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- === TAB KONTEN 4: Saran Harga === --}}
        <div id="tab-price" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3>💰 Saran Penyesuaian Harga</h3>
                </div>
                <div class="card-body table-responsive">
                    @php
                        $suggestions = collect($insights)->filter(fn($i) => str_contains($i['type'], 'Saran Harga'));
                    @endphp

                    @if($suggestions->isEmpty())
                        <p class="text-muted">Belum ada saran harga untuk saat ini.</p>
                    @else
                        <table class="insight-table" id="priceSuggestTable">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Saran</th>
                                </tr>
                            </thead>
                            <tbody id="priceSuggestBody">
                                @foreach($suggestions as $s)
                                    <tr>
                                        <td>{{ $s['type'] }}</td>
                                        <td>{{ $s['detail'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- 🔄 Pagination --}}
                        <div class="pagination-buttons text-center mt-3" style="margin-top:10px;">
                            <button id="prevPriceSuggest" class="btn btn-secondary btn-sm me-2">⬅ Prev</button>
                            <span id="pageInfoPriceSuggest" class="mx-2 fw-bold"></span>
                            <button id="nextPriceSuggest" class="btn btn-secondary btn-sm">Next ➡</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- === TAB KONTEN 5: Prediksi Penjualan === --}}
        <div id="tab-predict" class="tab-content">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>📈 Prediksi Penjualan & Stok Minggu Depan</h3>

                    {{-- 🔍 Search bar --}}
                    <div class="insight-controls" style="margin-top:10px;">
                        <input type="text" id="searchPredict" placeholder="🔍 Cari produk...">
                    </div>
                </div>

                <div class="card-body table-responsive">
                    <table class="insight-table" id="predictTable">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Perkiraan Penjualan</th>
                                <th>Prediksi Sisa Stok</th>
                            </tr>
                        </thead>
                        <tbody id="predictTableBody">
                            @foreach($predictions as $p)
                                <tr>
                                    <td>{{ $p['product'] }}</td>
                                    <td>{{ $p['predicted_sales'] }} unit</td>
                                    <td>{{ $p['predicted_stock'] }} unit</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- 🔄 Pagination --}}
                    <div class="pagination-buttons text-center mt-3" style="margin-top:10px;">
                        <button id="prevPredict" class="btn btn-secondary btn-sm me-2">⬅ Prev</button>
                        <span id="pageInfoPredict" class="mx-2 fw-bold"></span>
                        <button id="nextPredict" class="btn btn-secondary btn-sm">Next ➡</button>
                    </div>
                </div>
            </div>
        </div>

    @endif
</div>

{{-- ====== PURE CSS + JS UNTUK TAB DASHBOARD ====== --}}
<style>
    /* === Tab Navigation === */
    .tab-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 18px;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 6px;
    }

    .tab-btn {
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 600;
        font-size: 14px;
        color: #374151;
        transition: all 0.2s ease;
    }

    .tab-btn:hover {
        background: #e5e7eb;
    }

    .tab-btn.active {
        background: #2563eb;
        color: white;
        border-color: #2563eb;
    }

    /* === Tab Content === */
    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    .tab-content.active {
        display: block;
    }

    /* === Card Layout Fix === */
    .card {
        background-color: #ffffff !important;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        border: 1px solid #e5e7eb;
    }

    .card-header {
        padding: 14px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        font-weight: 600;
        color: #111827;
    }

    .card-body {
        padding: 18px;
    }

    /* === Insight Grid Cards === */
    .insight-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
    }

    .insight-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 18px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .insight-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }

    .insight-type {
        font-weight: 600;
        color: #111827;
        margin-bottom: 6px;
    }

    .insight-detail {
        color: #4b5563;
        font-size: 14px;
    }

    .insight-controls {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        gap: 10px;
    }

    .insight-controls input[type="text"],
    .insight-controls select {
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        outline: none;
        font-size: 14px;
    }

    /* === Tables === */
    .insight-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        color: #1f2937;
    }

    .insight-table th {
        background: #f3f4f6;
        font-weight: 600;
        text-align: left;
        border-bottom: 2px solid #e5e7eb;
        padding: 10px 12px;
    }

    .insight-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f1f1;
    }

    .insight-table tr:hover {
        background-color: #f9fafb;
    }

    .pagination-buttons {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 1rem;
    }

    .pagination-buttons button {
        padding: 8px 14px;
        border: none;
        background-color: #007bff;
        color: white;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
    }

    .pagination-buttons button:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }

    .search-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pagination-controls button {
        min-width: 80px;
    }

    #pageInfoPredict {
        font-size: 14px;
        color: #555;
    }

    /* === Badges === */
    .badge {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-danger {
        background: #fee2e2;
        color: #b91c1c;
    }

    .badge-warning {
        background: #fff7ed;
        color: #b45309;
    }

    .danger-row td {
        background-color: #fef2f2 !important;
    }

    .warning-row td {
        background-color: #fff7ed !important;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const tabs = document.querySelectorAll(".tab-btn");
        const contents = document.querySelectorAll(".tab-content");

        tabs.forEach(btn => {
            btn.addEventListener("click", () => {
                tabs.forEach(b => b.classList.remove("active"));
                contents.forEach(c => c.classList.remove("active"));

                btn.classList.add("active");
                document.getElementById(`tab-${btn.dataset.tab}`).classList.add("active");
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cards = Array.from(document.querySelectorAll('.insight-card'));
        const searchInput = document.getElementById('searchInsight');
        const filterSelect = document.getElementById('filterInsight');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const pageInfo = document.getElementById('pageInfo');

        let currentPage = 1;
        const perPage = 10;

        function renderCards() {
            let filtered = cards.filter(card => {
                const type = card.dataset.type;
                const detail = card.dataset.detail;
                const search = searchInput.value.toLowerCase();
                const filter = filterSelect.value;

                const matchSearch = !search || detail.includes(search);
                const matchFilter = filter === 'all' || type === filter;
                return matchSearch && matchFilter;
            });

            const totalPages = Math.ceil(filtered.length / perPage);
            if (currentPage > totalPages) currentPage = totalPages || 1;

            filtered.forEach(card => card.style.display = 'none');
            filtered.slice((currentPage - 1) * perPage, currentPage * perPage)
                .forEach(card => card.style.display = 'block');

            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages || totalPages === 0;
            pageInfo.textContent = `Halaman ${currentPage} dari ${totalPages || 1}`;
        }

        searchInput.addEventListener('input', () => {
            currentPage = 1;
            renderCards();
        });

        filterSelect.addEventListener('change', () => {
            currentPage = 1;
            renderCards();
        });

        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderCards();
            }
        });

        nextBtn.addEventListener('click', () => {
            currentPage++;
            renderCards();
        });

        renderCards();
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = Array.from(document.querySelectorAll('#predictTableBody tr'));
    const rowsPerPage = 10;
    let currentPage = 1;
    let filteredRows = [...rows];

    const prevBtn = document.getElementById('prevPredict');
    const nextBtn = document.getElementById('nextPredict');
    const pageInfo = document.getElementById('pageInfoPredict');
    const searchInput = document.getElementById('searchPredict');

    function showPage(page, data = filteredRows) {
        const totalPages = Math.ceil(data.length / rowsPerPage);
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach(r => r.style.display = 'none');
        data.slice(start, end).forEach(r => r.style.display = '');

        pageInfo.textContent = totalPages > 0 ? `Halaman ${page} dari ${totalPages}` : 'Tidak ada data';
        prevBtn.disabled = page === 1;
        nextBtn.disabled = page === totalPages || totalPages === 0;
    }

    function filterRows() {
        const term = searchInput.value.toLowerCase();
        filteredRows = rows.filter(r => r.textContent.toLowerCase().includes(term));
        currentPage = 1;
        showPage(currentPage, filteredRows);
    }

    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            showPage(currentPage);
        }
    });

    nextBtn.addEventListener('click', () => {
        if (currentPage < Math.ceil(filteredRows.length / rowsPerPage)) {
            currentPage++;
            showPage(currentPage);
        }
    });

    searchInput.addEventListener('input', filterRows);

    showPage(currentPage);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = Array.from(document.querySelectorAll('#lowStockBody tr'));
    const rowsPerPage = 10;
    let currentPage = 1;

    const prevBtn = document.getElementById('prevLowStock');
    const nextBtn = document.getElementById('nextLowStock');
    const pageInfo = document.getElementById('pageInfoLowStock');

    function showPage(page) {
        const totalPages = Math.ceil(rows.length / rowsPerPage);
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach(r => r.style.display = 'none');
        rows.slice(start, end).forEach(r => r.style.display = '');

        pageInfo.textContent = totalPages > 0 ? `Halaman ${page} dari ${totalPages}` : 'Tidak ada data';
        prevBtn.disabled = page === 1;
        nextBtn.disabled = page === totalPages || totalPages === 0;
    }

    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            showPage(currentPage);
        }
    });

    nextBtn.addEventListener('click', () => {
        if (currentPage < Math.ceil(rows.length / rowsPerPage)) {
            currentPage++;
            showPage(currentPage);
        }
    });

    showPage(currentPage);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = Array.from(document.querySelectorAll('#priceSuggestBody tr'));
    const rowsPerPage = 10;
    let currentPage = 1;

    const prevBtn = document.getElementById('prevPriceSuggest');
    const nextBtn = document.getElementById('nextPriceSuggest');
    const pageInfo = document.getElementById('pageInfoPriceSuggest');

    function showPage(page) {
        const totalPages = Math.ceil(rows.length / rowsPerPage);
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach(r => r.style.display = 'none');
        rows.slice(start, end).forEach(r => r.style.display = '');

        pageInfo.textContent = totalPages > 0 ? `Halaman ${page} dari ${totalPages}` : 'Tidak ada data';
        prevBtn.disabled = page === 1;
        nextBtn.disabled = page === totalPages || totalPages === 0;
    }

    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            showPage(currentPage);
        }
    });

    nextBtn.addEventListener('click', () => {
        if (currentPage < Math.ceil(rows.length / rowsPerPage)) {
            currentPage++;
            showPage(currentPage);
        }
    });

    showPage(currentPage);
});
</script>
@endsection