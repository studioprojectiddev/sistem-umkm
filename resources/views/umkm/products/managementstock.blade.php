@extends('layouts.app')

@section('title', 'Management Stock')

@section('content')
<style>
/* ============ GLOBAL LAYOUT ============ */
.page-wrapper {
    max-width: 1300px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, sans-serif;
    color: #2c3e50;
}
h1.title {
    font-size: 1.8rem;
    margin-bottom: 4px;
}
.breadcrumbs {
    display: flex;
    gap: 6px;
    margin-bottom: 25px;
    font-size: 0.9rem;
}
.breadcrumbs a {
    text-decoration: none;
    color: #3498db;
}
.breadcrumbs a.active { color: #777; }

/* ============ SUMMARY CARDS ============ */
.summary-row {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(260px,1fr));
    gap: 18px;
    margin-bottom: 25px;
}
.summary-card {
    padding: 20px;
    border-radius: 12px;
    color: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: transform .2s ease;
}
.summary-card:hover { transform: translateY(-4px); }
.summary-card h5 {
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 1rem;
}
.summary-card h3 {
    font-size: 1.8rem;
    margin: 0;
    font-weight: 700;
}
.bg-primary { background: #3498db; }
.bg-success { background: #2ecc71; }
.bg-warning { background: #f1c40f; color: #2c3e50; }

/* ============ FILTER BOX ============ */
.filter-box {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    margin-bottom: 25px;
}
.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}
.filter-row input[type="text"],
.filter-row select {
    padding: 10px 14px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 0.95rem;
    min-width: 220px;
}
.filter-row button {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    background: #3498db;
    color: #fff;
    cursor: pointer;
    font-size: 0.95rem;
    transition: background 0.2s ease;
}
.filter-row button:hover { background: #2980b9; }

/* ============ TABLE ============ */
.table-container {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.custom-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
}
.custom-table thead {
    background: #f7f7f7;
}
.custom-table th,
.custom-table td {
    padding: 12px 14px;
    text-align: left;
    border-bottom: 1px solid #e6e6e6;
}
.custom-table th {
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: .5px;
    color: #555;
}
.custom-table tbody tr:hover {
    background: #fafafa;
}
.product-name {
    font-weight: 600;
    color: #2c3e50;
}
.variant-list {
    list-style: none;
    margin: 6px 0 0 0;
    padding: 0;
    font-size: 0.85rem;
    color: #555;
}
.variant-list li::before {
    content: "• ";
    color: #999;
}
.badge-low {
    display: inline-block;
    padding: 2px 6px;
    margin-left: 4px;
    background: #e74c3c;
    color: #fff;
    border-radius: 6px;
    font-size: 0.7rem;
}

/* ============ BUTTONS ============ */
.btn-group {
    display: inline-flex;
    gap: 6px;
}
.btn {
    padding: 6px 10px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.85rem;
    cursor: pointer;
    transition: background .2s ease;
}
.btn-warning { background: #f39c12; color: #fff; }
.btn-warning:hover { background: #d68910; }
.btn-info { background: #3498db; color: #fff; }
.btn-info:hover { background: #2980b9; }

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 999;
}
.modal-overlay.hidden { display: none; }

.modal-box {
    background: #fff;
    padding: 20px;
    width: 600px;
    max-height: 80vh;
    overflow-y: auto;
    border-radius: 8px;
    box-shadow: 0 6px 20px rgba(0,0,0,.3);
    animation: fadeIn .3s ease;
}
.modal-header {
    display: flex;
    justify-content: space-between; /* Pastikan X tetap di kanan */
    align-items: center;
    position: sticky;  /* agar tidak ketimpa ketika scroll */
    top: 0;
    background: #fff;
    z-index: 10;       /* pastikan di atas konten lain */
    padding-bottom: 8px;
}
.close-btn {
    background: none;
    border: none;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.detail-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
}
.detail-table th,
.detail-table td {
    padding: 8px 10px;
    border: 1px solid #ddd;
    text-align: left;
}
.detail-table th {
    background: #f7f7f7;
    width: 40%;
}
.mt-2 { margin-top: 15px; }
@keyframes fadeIn {
    from {opacity: 0; transform: translateY(-10px);}
    to   {opacity: 1; transform: translateY(0);}
}

/* ============ RESPONSIVE ============ */
@media(max-width:768px){
    .filter-row { flex-direction: column; }
    .custom-table th, .custom-table td { font-size: 0.85rem; padding: 10px; }
}
</style>
<h1 class="title">Management Stock</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.product.management_stock') }}" class="active">Management Stock</a></li>
</ul>
<div class="page-wrapper">

    {{-- ===== SUMMARY CARD ===== --}}
    <div class="summary-row">
        <div class="summary-card bg-primary">
            <h5>Total Stok Produk (Non-Varian)</h5>
            <h3>{{ number_format($summary['product_stock']) }}</h3>
        </div>
        <div class="summary-card bg-success">
            <h5>Total Stok Varian</h5>
            <h3>{{ number_format($summary['variant_stock']) }}</h3>
        </div>
        <div class="summary-card bg-warning">
            <h5>Produk Stok Menipis (&lt; 10)</h5>
            <h3>{{ number_format($summary['low_stock']) }}</h3>
        </div>
    </div>

    {{-- ===== FILTER ===== --}}
    <div class="filter-box">
        <form method="GET" action="{{ route('umkm.product.management_stock') }}">
            <div class="filter-row">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / SKU...">
                <button type="submit">Filter</button>
            </div>
        </form>
    </div>

    {{-- ===== TABLE ===== --}}
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produk</th>
                    <th>SKU</th>
                    <th>Harga</th>
                    <th>Stok Produk</th>
                    <th>Stok Varian</th>
                    <th>Total Stok</th>
                    <th>Total Terjual</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $i => $product)
                <tr>
                    <td>{{ $products->firstItem() + $i }}</td>
                    <td>
                        <span class="product-name">{{ $product->name }}</span>
                        @if($product->variations->count())
                            <ul class="variant-list">
                                @foreach($product->variations as $v)
                                    <li>
                                        {{ $v->name }} — Stok {{ number_format($v->stock) }}
                                        @if($v->stock < 10)
                                            <span class="badge-low">Low</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td>{{ $product->sku ?? '-' }}</td>
                    <td>Rp {{ number_format($product->price ?? 0) }}</td>
                    <td>{{ number_format($product->stock_product) }}</td>
                    <td>{{ number_format($product->stock_variants) }}</td>
                    <td><strong>{{ number_format($product->stock_total) }}</strong></td>
                    <td>{{ number_format($product->total_sold) }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="javascript:void(0)"
                            class="btn btn-info btn-detail"
                            data-id="{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-sku="{{ $product->sku ?? '-' }}"
                            data-price="{{ $product->price ?? 0 }}"
                            data-stock-product="{{ $product->stock_product }}"
                            data-stock-variants="{{ $product->stock_variants }}"
                            data-stock-total="{{ $product->stock_total }}"
                            data-total-sold="{{ $product->total_sold }}"
                            data-variations='@json($product->variations)'
                            >Detail</a>
                            <a href="#"
                            class="btn btn-warning btn-edit"
                            data-id="{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-sku="{{ $product->sku }}"
                            data-price="{{ $product->price }}"
                            data-stock="{{ $product->stock }}"
                            data-url="{{ route('products.update', $product->id) }}">Edit</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;color:#888;">Tidak ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:20px;">
            {{ $products->withQueryString()->links() }}
        </div>
    </div>
</div>

<div id="modalDetail" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="detailName"></h3>
            <button class="close-btn" id="closeModal">✖</button>
        </div>
        <div class="modal-content">
            <table class="detail-table">
                <tr><th>SKU</th><td id="detailSku"></td></tr>
                <tr><th>Harga Produk</th><td id="detailPrice"></td></tr>
                <tr><th>Stok Produk</th><td id="detailStockProduct"></td></tr>
                <tr><th>Stok Varian</th><td id="detailStockVariants"></td></tr>
                <tr><th>Total Stok</th><td id="detailStockTotal"></td></tr>
                <tr><th>Total Terjual</th><td id="detailTotalSold"></td></tr>
            </table>

            <h4 class="mt-2">Daftar Varian</h4>
            <table class="detail-table" id="variantTable">
                <thead>
                    <tr>
                        <th>Nama Varian</th>
                        <th>Harga</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalEdit" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Produk</h3>
            <button class="close-btn" id="closeEdit">✖</button>
        </div>
        <div class="modal-content">
            <form id="editForm" method="POST" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="editId">

                <label>Nama Produk</label>
                <input type="text" name="name" id="editName" required>

                <label>SKU</label>
                <input type="text" name="sku" id="editSku" required>

                <label>Harga</label>
                <input type="number" name="price" id="editPrice" required>

                <label>Stok Produk</label>
                <input type="number" name="stock" id="editStock" required>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>  

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal      = document.getElementById('modalDetail');
    const closeBtn   = document.getElementById('closeModal');
    const variantTbody = document.querySelector('#variantTable tbody');

    // buka modal
    document.querySelectorAll('.btn-detail').forEach(btn => {
        btn.addEventListener('click', function () {
            // isi data produk
            document.getElementById('detailName').textContent        = this.dataset.name;
            document.getElementById('detailSku').textContent         = this.dataset.sku;
            document.getElementById('detailPrice').textContent       = 'Rp ' + parseInt(this.dataset.price).toLocaleString();
            document.getElementById('detailStockProduct').textContent= this.dataset.stockProduct;
            document.getElementById('detailStockVariants').textContent= this.dataset.stockVariants;
            document.getElementById('detailStockTotal').textContent  = this.dataset.stockTotal;
            document.getElementById('detailTotalSold').textContent   = this.dataset.totalSold;

            // isi varian
            variantTbody.innerHTML = '';
            let variants = JSON.parse(this.dataset.variations || '[]');
            if (variants.length) {
                variants.forEach(v => {
                    let tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${v.name}</td>
                        <td>Rp ${parseInt(v.price).toLocaleString()}</td>
                        <td>${v.stock}</td>
                    `;
                    variantTbody.appendChild(tr);
                });
            } else {
                let tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="3" style="text-align:center;color:#777">Tidak ada varian</td>`;
                variantTbody.appendChild(tr);
            }

            modal.classList.remove('hidden');
        });
    });

    // tutup modal
    closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
    modal.addEventListener('click', e => {
        if (e.target === modal) modal.classList.add('hidden');
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalEdit  = document.getElementById('modalEdit');
    const closeEdit  = document.getElementById('closeEdit');
    const formEdit   = document.getElementById('editForm');

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();

            // isi form dengan data produk
            document.getElementById('editId').value    = btn.dataset.id;
            document.getElementById('editName').value  = btn.dataset.name;
            document.getElementById('editSku').value   = btn.dataset.sku;
            document.getElementById('editPrice').value = btn.dataset.price;
            document.getElementById('editStock').value = btn.dataset.stock;

            // set action form
            formEdit.action = btn.dataset.url;

            modalEdit.classList.remove('hidden');
        });
    });

    // tutup modal
    closeEdit.addEventListener('click', () => modalEdit.classList.add('hidden'));
    modalEdit.addEventListener('click', e => {
        if (e.target === modalEdit) modalEdit.classList.add('hidden');
    });
});
</script>
@endsection
