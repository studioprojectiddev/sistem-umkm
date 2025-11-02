@extends('layouts.app')

@section('title', 'POS (Kasir)')

@section('content')

<style>
/* Tabel */
.table-container {
    margin-top: 20px;
    overflow-x: auto;
}

.custom-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.custom-table th,
.custom-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #e0e0e0;
    text-align: left;
    white-space: nowrap;
}

.custom-table thead {
    background: #f8f8f8;
}

.badge {
    padding: 3px 8px;
    border-radius: 5px;
    font-weight: 600;
    font-size: 12px;
}
.badge-success {
    background: #d4edda;
    color: #155724;
}
.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

/* Pagination */
.pagination-container {
    margin-top: 16px;
    text-align: center;
}

.pagination-container button {
    background: #f0f0f0;
    border: none;
    padding: 6px 12px;
    margin: 0 3px;
    border-radius: 4px;
    cursor: pointer;
    transition: 0.2s;
}

.pagination-container button.active {
    background: #4CAF50;
    color: white;
    font-weight: bold;
}

.pagination-container button:hover {
    background: #ddd;
}

/* --- Layout umum --- */
.page-title {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

/* --- Stats Box --- */
.stats-container {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 25px;
}
.stat-box {
    flex: 1;
    min-width: 200px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    transition: transform 0.2s;
}
.stat-box:hover { transform: translateY(-3px); }
.stat-label {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 5px;
}
.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
}
.stat-value.primary { color: #007bff; }
.stat-value.success { color: #28a745; }

/* --- Card --- */
.card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 10px;
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.card-header {
    background: #f7f7f7;
    padding: 12px 16px;
    font-weight: 600;
    border-bottom: 1px solid #ddd;
}
.card-header.flex-between {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.card-body {
    padding: 16px;
}

/* --- Form --- */
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}
.form-group {
    display: flex;
    flex-direction: column;
}
.form-group.full {
    grid-column: 1 / -1;
}
.form-actions {
    text-align: right;
}
label {
    font-size: 0.9rem;
    color: #444;
    margin-bottom: 6px;
}
input[type="text"],
input[type="number"],
select,
textarea {
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.2s;
}
input:focus,
select:focus,
textarea:focus {
    border-color: #007bff;
}

/* --- Buttons --- */
.btn-primary,
.btn-secondary {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 0.95rem;
    cursor: pointer;
    border: none;
    transition: background 0.2s;
}
.btn-primary {
    background: #007bff;
    color: #fff;
}
.btn-primary:hover { background: #0056b3; }

.btn-secondary {
    background: #6c757d;
    color: #fff;
}
.btn-secondary:hover { background: #565e64; }

/* --- Table --- */
.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 0.95rem;
}
.data-table th,
.data-table td {
    border: 1px solid #ddd;
    padding: 10px 8px;
    text-align: left;
}
.data-table th {
    background: #f1f1f1;
    font-weight: 600;
}
.data-table tr:nth-child(even) {
    background: #fafafa;
}

/* --- Utilities --- */
.mt-20 { margin-top: 20px; }

/* ====== BUTTON STYLING ====== */
.btn {
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all .25s ease;
    box-shadow: 0 3px 6px rgba(0,0,0,0.15);
}

/* Warna utama */
.btn.success { 
    background: linear-gradient(135deg, #2c7be5, #1a5dbf); 
    color: #fff; 
}
.btn.success:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 5px 10px rgba(44,123,229,0.4); 
}

/* Warna peringatan */
.btn.warning { 
    background: linear-gradient(135deg, #ffc107, #e0a800); 
    color: #000; 
}
.btn.warning:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 5px 10px rgba(255,193,7,0.4); 
}

/* Warna bahaya */
.btn.danger { 
    background: linear-gradient(135deg, #dc3545, #b52a37); 
    color: #fff; 
}
.btn.danger:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 5px 10px rgba(220,53,69,0.4); 
}

/* ====== INPUT QTY STYLING ====== */
.qty-box {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.qty-btn {
    background: #f1f3f5;
    border: 1px solid #ccc;
    border-radius: 6px;
    width: 28px;
    height: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: all .2s;
}
.qty-btn:hover {
    background: #e9ecef;
}

.input.qty {
    width: 60px;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 6px;
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    color: #2c7be5;
    outline: none;
}
.input.qty:focus {
    border-color: #2c7be5;
    box-shadow: 0 0 5px rgba(44,123,229,0.3);
}

/* ====== STYLING UNTUK INPUT QTY ====== */
.form-control.qty {
    width: 70px;
    padding: 6px 8px;
    border: 1px solid #ccc;
    border-radius: 8px;
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    color: #2c7be5;
    background: #fff;
    outline: none;
    transition: all .25s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.form-control.qty:focus {
    border-color: #2c7be5;
    box-shadow: 0 0 6px rgba(44,123,229,0.35);
}

/* Hilangkan default panah spinner biar lebih clean */
.form-control.qty::-webkit-inner-spin-button, 
.form-control.qty::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.form-control.qty {
    -moz-appearance: textfield; /* Firefox */
}

/* Container card */
.form-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.form-title {
    margin-bottom: 15px;
    color: #333;
}

/* Grid system */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 15px;
}

/* Full width (misalnya untuk select dan harga akhir) */
.form-group.full {
    grid-column: span 2;
}

/* Input & Select style */
.form-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 0.9rem;
    color: #444;
    font-weight: 600;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 0.95rem;
    transition: border 0.2s ease;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #007bff;
    outline: none;
}

/* Checkbox */
.form-check {
    margin-bottom: 15px;
}

.promo-box {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    border: 1px dashed #ccc;
    margin-bottom: 15px;
}

/* Submit button */
.form-actions {
    text-align: right;
}

.form-actions button {
    background: #28a745;
    color: white;
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.2s ease;
}

.form-actions button:hover {
    background: #218838;
}

.swal-popup-custom {
    border-radius: 12px;
    padding: 20px;
}
.swal-container {
    text-align: left;
    font-size: 15px;
}
.swal-info-box {
    background: #f8f9fa;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 16px;
}
.swal-row {
    display: flex;
    justify-content: space-between;
    padding: 4px 0;
    border-bottom: 1px dashed #e9ecef;
}
.swal-row:last-child {
    border-bottom: none;
}
.swal-row .label {
    font-weight: 600;
    color: #555;
}
.swal-row .badge {
    background: #17a2b8;
    color: #fff;
    padding: 2px 8px;
    border-radius: 6px;
}
.swal-input-box {
    margin-top: 8px;
}
.swal-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
}
.swal-input {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
    outline: none;
    transition: border-color 0.3s;
}
.swal-input:focus {
    border-color: #007bff;
}
/* Style khusus input date */
input[type="date"] {
  -webkit-appearance: none;  /* hilangkan style bawaan Safari/Chrome */
  -moz-appearance: none;     /* hilangkan style bawaan Firefox */
  appearance: none;

  width: 100%;
  max-width: 220px;          /* atur lebar sesuai layout */
  padding: 8px 12px;
  font-size: 14px;
  font-family: inherit;

  color: #1f2937;            /* abu tua */
  background-color: #ffffff; /* putih bersih */

  border: 1px solid #d1d5db; /* abu muda */
  border-radius: 8px;

  box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
  transition: all 0.15s ease;
}

/* Efek saat fokus */
input[type="date"]:focus {
  outline: none;
  border-color: #2563eb;     /* biru */
  box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
}

/* Placeholder (tanggal kosong) */
input[type="date"]::-webkit-datetime-edit-placeholder {
  color: #9ca3af;
  font-size: 13px;
}

/* Hilangkan ikon kalender default dan ganti padding */
input[type="date"]::-webkit-calendar-picker-indicator {
  background: url("data:image/svg+xml,%3Csvg fill='none' stroke='%236b7280' stroke-width='2' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M8 7V3m8 4V3m-9 8h10m-12 5h14a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v7a2 2 0 002 2z'/%3E%3C/svg%3E") no-repeat center;
  background-size: 18px 18px;
  color: transparent;   /* sembunyikan default arrow */
  opacity: 0.7;
  width: 24px;
  height: 24px;
  cursor: pointer;
}

input[type="date"]::-webkit-calendar-picker-indicator:hover {
  opacity: 1;
}

#supportfitur {
  padding: 20px;
  color: #1f2937; /* abu tua */
  font-family: "Inter", sans-serif;
}

.support-title {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 10px;
}

.support-subtitle {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 25px;
}

/* Grid Card */
.support-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
  margin-bottom: 30px;
}

.support-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 18px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.04);
  transition: transform 0.15s ease;
}

.support-card:hover {
  transform: translateY(-3px);
  border-color: #2563eb;
}

.support-card h4 {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 6px;
}

.support-card p {
  font-size: 14px;
  color: #4b5563;
}

/* Kontak Support */
.support-contact {
  padding: 20px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
}

.support-contact h4 {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 8px;
}

.support-contact p {
  font-size: 14px;
  color: #4b5563;
  margin-bottom: 10px;
}

.support-contact a {
  color: #2563eb;
  text-decoration: none;
}

.support-contact a:hover {
  text-decoration: underline;
}

.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
    flex-wrap: wrap;
}

.table-info {
    font-size: 14px;
    color: #555;
    margin-bottom: 8px;
}

.search-container {
    margin-bottom: 16px;
}

#searchStock {
    width: 100%;
    max-width: 350px;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

#searchStock:focus {
    border-color: #4a90e2;
    box-shadow: 0 0 6px rgba(74, 144, 226, 0.3);
    outline: none;
}

/* Responsive */
@media (max-width: 768px) {
    .custom-table th, .custom-table td {
        padding: 8px;
        font-size: 12px;
    }
    .pagination-container button {
        padding: 5px 8px;
        font-size: 12px;
    }
    .table-footer {
        flex-direction: column;
        align-items: flex-start;
    }
}

</style>


<h1 class="title">Point Of Sales</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="divider">/</li>
    <li><a href="{{ route('umkm.pos.index') }}" class="active">POS</a></li>
</ul>

<div class="info-data">
    <div class="card">
        {{-- Tabs Menu --}}
        <div class="tabs-menu">
            <button class="tab-link active" data-target="identitasproduk">Identitas Produk Lengkap</button>
            <button class="tab-link" data-target="hargadiskon">Harga & Variasi Diskon</button>
            <button class="tab-link" data-target="stokreal">Stok Real-time & Unit</button>
            <button class="tab-link" data-target="aksikasir">Aksi Kasir</button>
            <!-- <button class="tab-link" data-target="integrasitransaksi">Integrasi Transaksi & Update Stok</button> -->
            <button class="tab-link" data-target="laporanproduk">Laporan & Analitik Produk</button>
            <!-- <button class="tab-link" data-target="supportfitur">Support Fitur Tambahan</button> -->
        </div>

        {{-- Tab: Identitas Produk --}}
        <div id="identitasproduk" class="tab-pane active">

            <div style="margin-bottom:10px;">
                <input type="text" id="productSearch" class="form-control" placeholder="Cari nama produk...">
            </div>
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Thumbnail</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>SKU / Barcode</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi Kasir</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        @foreach($products as $index => $product)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if($product->thumbnail)
                                    <img src="{{ asset($product->thumbnail) }}" alt="{{ $product->name }}" width="50">
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                            <td>
                                {{-- Nama produk utama --}}
                                <strong>{{ $product->name }}</strong>

                                {{-- Jika produk punya variasi --}}
                                @if($product->variations->count())
                                    <ul class="mb-0 ps-3">
                                        @foreach($product->variations as $vindex => $var)
                                            <li>
                                                {{ $vindex + 1 }}.  
                                                (Stok: {{ $var->stock }},
                                                Rp {{ number_format($var->price, 0, ',', '.') }},
                                                {{ $var->weight ?? 0 }} gr)

                                                @if($var->options->count())
                                                    <br>
                                                    <small class="text-muted">
                                                        [
                                                        @foreach($var->options as $opt)
                                                            {{ $opt->attribute->name }}: {{ $opt->value }}@if(!$loop->last), @endif
                                                        @endforeach
                                                        ]
                                                    </small>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    {{-- Produk tanpa variasi (non-variasi) --}}
                                    <ul class="mb-0 ps-3">
                                        <li>
                                            (Stok: {{ $product->stock }},
                                            Rp {{ number_format($product->price ?? 0, 0, ',', '.') }},
                                            {{ $product->weight ?? 0 }} gr)
                                        </li>
                                    </ul>
                                @endif
                            </td>

                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>{{ $product->sku ?? $product->barcode ?? '-' }}</td>

                            <td>
                                @if($product->is_promo && $product->promo_start <= now() && $product->promo_end >= now())
                                    <span class="text-success fw-bold">
                                        Rp {{ number_format($product->final_price, 0, ',', '.') }}
                                    </span><br>
                                    <small class="text-muted">
                                        <del>Rp {{ number_format($product->price, 0, ',', '.') }}</del>
                                        <br>Promo: {{ \Carbon\Carbon::parse($product->promo_start)->format('d M') }} -
                                        {{ \Carbon\Carbon::parse($product->promo_end)->format('d M Y') }}
                                    </small>
                                @else
                                    Rp {{ number_format($product->final_price, 0, ',', '.') }}
                                @endif
                            </td>

                            <td>{{ $product->stockProduct }}</td>

                            <td>
                                <button class="btn btn-sm btn-success btn-add-cart"
                                    data-id="{{ $product->id }}"
                                    data-price="{{ $product->price }}"
                                    data-final-price="{{ $product->final_price }}"
                                    data-has-variation="{{ $product->variations->count() > 0 ? 1 : 0 }}"
                                    data-variations='@json($product->variation_json)'>
                                    + Keranjang
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="table-footer">
                    <div id="tableInfo" class="table-info"></div>
                    <div id="tablePaginationProduct" class="pagination-container"></div>
                </div>
            </div>
        </div>

        {{-- Tab: Harga & Diskon --}}
        <div id="hargadiskon" class="tab-pane">
            <div class="form-card">
               
                {{-- Pilih Produk --}}
                <div class="form-group full">
                    <label for="select_product">Pilih Produk</label>
                    <select id="select_product">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}"
                                data-price="{{ $p->price }}"
                                data-cost="{{ $p->cost_price }}"
                                data-discount="{{ $p->discount_price }}"
                                data-ispromo="{{ $p->is_promo }}"
                                data-promoprice="{{ $p->promo_price }}"
                                data-promostart="{{ $p->promo_start }}"
                                data-promoend="{{ $p->promo_end }}">
                                {{ $p->name }} ({{ $p->sku ?? 'No SKU' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <form id="discountForm" method="POST" action="{{ route('umkm.pos.discount') }}">
                    @csrf
                    <input type="hidden" name="product_id" id="product_id">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="base_price">Harga Dasar (Rp)</label>
                            <input type="number" id="base_price" name="price" readonly>
                        </div>
                        <div class="form-group">
                            <label for="cost_price">Harga Modal (Rp)</label>
                            <input type="number" id="cost_price" name="cost_price" readonly>
                        </div>
                    </div>

                    <!-- <div class="form-group full">
                        <label for="final_price">Harga Setelah Diskon (Rp)</label>
                        <input type="text" id="final_price" name="discount_price" readonly>
                    </div> -->
                    <br>

                    <hr><br>

                    <div class="form-check">
                        <input type="checkbox" id="is_promo" name="is_promo" value="1">
                        <label for="is_promo">Aktifkan Promo</label>
                    </div>

                    <div id="promo_section" class="promo-box" style="display:none;">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="promo_price">Harga Promo (Rp)</label>
                                <input type="number" id="promo_price" name="promo_price">
                            </div>
                            <div class="form-group">
                                <label for="promo_start">Promo Mulai</label>
                                <input type="date" id="promo_start" name="promo_start">
                            </div>
                            <div class="form-group">
                                <label for="promo_end">Promo Selesai</label>
                                <input type="date" id="promo_end" name="promo_end">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit">💾 Simpan</button>
                    </div>
                </form>
            </div>
        </div>


        {{-- Tab: Stok Real-time --}}
        <div id="stokreal" class="tab-pane">
            <div class="form-card">
                
                <!-- Search & Filter -->
                <div class="search-container">
                    <input type="text" id="searchStock" placeholder="🔍 Cari produk, SKU, atau variasi...">
                </div>

                <!-- Table Stok -->
                <div class="table-container">
                    <table class="custom-table" id="stockTable">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Variasi / Unit</th>
                                <th>Stok Tersedia</th>
                                <th>Terjual</th>
                                <th>Minimal Stok</th>
                                <th>Status</th>
                                <!-- <th>Aksi</th> -->
                            </tr>
                        </thead>
                        <tbody id="stockTableBody">
                        {{-- contoh di dalam loop product --}}
                            @foreach($products as $p)
                                <tr>
                                    <td>{{ $p->name }} ({{ $p->sku ?? 'No SKU' }})</td>
                                    <td>
                                        @if($p->variations->isEmpty())
                                            -
                                        @else
                                            {{ $p->variations->count() }} variasi
                                        @endif
                                    </td>
                                    <td>{{ $p->stockProduct }}</td>
                                    <td>{{ $p->total_sold ?? 0 }}</td>
                                    <td>{{ $p->min_stock ?? 0 }}</td>
                                    <td>
                                        @if($p->stockProduct <= ($p->min_stock ?? 5))
                                            <span class="badge badge-danger">⚠️ Hampir Habis</span>
                                        @else
                                            <span class="badge badge-success">✔️ Aman</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- variasi --}}
                                @foreach($p->variation_json as $v)
                                    <tr>
                                        <td>{{ $p->name }} ({{ $p->sku ?? 'No SKU' }})</td>
                                        <td>
                                            {{ $v['name'] }}
                                            @if($v['weight']) [ {{ number_format($v['weight']) }} gr ] @endif
                                            @if(count($v['options']) > 0)
                                                ({{ implode(' / ', array_column($v['options'], 'value')) }})
                                            @endif
                                        </td>
                                        <td>{{ $v['stock'] }}</td>
                                        <td>{{ $v['sold'] ?? 0 }}</td>
                                        <td>{{ $v['min_stock'] ?? 5 }}</td>
                                        <td>
                                            @if($v['stock'] <= ($v['min_stock'] ?? 5))
                                                <span class="badge badge-danger">⚠️ Hampir Habis</span>
                                            @else
                                                <span class="badge badge-success">✔️ Aman</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>

                    </table>
                    <div id="tablePagination" class="pagination-container"></div>
                    <!-- Pagination Controls + Total Data -->
                    <div class="table-footer">
                        <div id="tableInfo" class="table-info"></div>
                        <div id="tablePagination" class="pagination-container"></div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Tab: Aksi Kasir --}}
        <div id="aksikasir" class="tab-pane">
            <table class="table" id="cartTable">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart as $id => $item)
                    <tr data-id="{{ $id }}">
                        <td>
                            {{ $item['name'] }}
                            @if(!empty($item['variation']))
                                <br><small class="text-muted">{{ $item['variation'] }}</small>
                            @endif
                        </td>
                        <td><input type="number" value="{{ $item['quantity'] }}" class="form-control qty"></td>
                        <td>Rp {{ number_format($item['subtotal'],0,',','.') }}</td>
                        <td><button class="btn btn-danger btn-sm remove">X</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:10px;text-align:right;">
                <button class="btn btn-success" id="checkout">Checkout</button>&nbsp;&nbsp;
                <button class="btn btn-warning" id="clearCart">Kosongkan</button>
            </div>    
        </div>

        <div id="integrasitransaksi" class="tab-pane">

            <!-- Ringkasan Stok -->
            <div class="stats-container">
                <div class="stat-box">
                    <div class="stat-label">Total Produk</div>
                    <div class="stat-value" id="totalProduk">0</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Total Stok</div>
                    <div class="stat-value primary" id="totalStok">0</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Transaksi Hari Ini</div>
                    <div class="stat-value success" id="totalTransaksi">0</div>
                </div>
            </div>

            <!-- Form Update Stok -->
            <div class="card">
                <div class="card-header">Tambah / Kurangi Stok</div>
                <div class="card-body">
                    <form id="formUpdateStock" class="form-grid">
                        <div class="form-group">
                            <label for="product_id">Produk / Variasi</label>
                            <select id="product_id" name="product_id" required>
                                <option value="">Pilih Produk...</option>
                                @foreach($products as $p)
                                    <option value="product-{{ $p->id }}">🛍️ {{ $p->name }}</option>
                                    @foreach($p->variations as $v)
                                        @php
                                            $weight  = $v->weight ? number_format($v->weight).' g' : '';
                                            $options = $v->options->pluck('value')->implode(' / ');
                                            $extra   = trim($weight.' '.$options);
                                        @endphp
                                        <option value="variation-{{ $v->id }}">
                                            ↳ {{ $p->name }} {{ $extra ? '('.$extra.')' : '' }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="transaction_type">Jenis Transaksi</label>
                            <select id="transaction_type" name="transaction_type" required>
                                <option value="">Pilih...</option>
                                <option value="in">Tambah Stok (Barang Masuk)</option>
                                <option value="out">Kurangi Stok (Barang Keluar)</option>
                                <option value="adjust">Koreksi Stok</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="quantity">Jumlah</label>
                            <input type="number" id="quantity" name="quantity" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="supplier">Supplier (Opsional)</label>
                            <input type="text" id="supplier" name="supplier" placeholder="Nama Supplier">
                        </div>

                        <div class="form-group full">
                            <label for="note">Catatan / Keterangan</label>
                            <textarea id="note" name="note" rows="2"></textarea>
                        </div>

                        <div class="form-actions full">
                            <button type="submit" class="btn-primary">Simpan Transaksi</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Riwayat Transaksi -->
            <div class="card mt-20">
                <div class="card-header flex-between">
                    <span>Riwayat Transaksi Stok</span>
                    <button class="btn-secondary" id="refreshTransaksi">Refresh</button>
                </div>
                <div class="card-body">
                    <div class="filter-container" style="margin-bottom: 15px; display:flex; gap:10px; flex-wrap:wrap;">
                        <label>Dari:</label>
                        <input type="date" id="filterStart" value="{{ date('Y-m-01') }}">
                        <label>Sampai:</label>
                        <input type="date" id="filterEnd" value="{{ date('Y-m-d') }}">
                        <button class="btn-primary" id="btnFilter">Terapkan</button>
                    </div>

                    <table class="data-table" id="tableTransaksi">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Produk / Variasi</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th>Jumlah</th>
                                <th>Supplier</th>
                                <th>Catatan</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody id="transaksiBody">
                        </tbody>
                    </table>

                    <div class="table-footer">
                        <div id="transaksiInfo" class="table-info"></div>
                        <div id="transaksiPagination" class="pagination-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="laporanproduk" class="tab-pane">

            <!-- Filter & Rentang Tanggal -->
            <div class="filter-container">
                <label>Dari:</label>
                <input type="date" class="form-control" id="filterStart" value="{{ date('Y-m-01') }}">
                <label>Sampai:</label>
                <input type="date" class="form-control" id="filterEnd" value="{{ date('Y-m-d') }}">
                <button class="btn-primary" id="btnFilter">Terapkan</button>
            </div><br>

            <!-- Ringkasan Statistik -->
            <div class="stats-container mt-4">
                <div class="stat-box">
                    <div class="stat-label">Produk Terlaris</div>
                    <div class="stat-value" id="bestProduct">-</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Total Penjualan</div>
                    <div class="stat-value primary" id="totalSales">0</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Transaksi Stok</div>
                    <div class="stat-value success" id="totalStockMovement">0</div>
                </div>
            </div>

            <!-- Grafik Tren Penjualan -->
            <div class="card mt-4">
                <div class="card-header">Tren Penjualan</div>
                <div class="card-body">
                    <canvas id="chartPenjualan" height="120"></canvas>
                </div>
            </div>

            <!-- Tabel Laporan Detail -->
            <div class="card mt-4">
                <div class="card-header">Detail Laporan Produk</div>
                <div class="card-body">
                    <table class="data-table" id="tableLaporanProduk">
                        <thead>
                            <tr>
                                <th>Tipe</th>
                                <th>Produk / Variasi</th>
                                <th>Total Terjual</th>
                                <th>Stok Tersedia</th>
                                <th>Stok Akhir</th>
                                <th>Masuk</th>
                                <th>Keluar</th>
                                <th>Adjust</th>
                                <th>Pendapatan (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="laporanProdukBody"></tbody>
                    </table>

                    <div class="table-footer">
                        <div id="laporanProdukInfo" class="table-info"></div>
                        <div id="laporanProdukPagination" class="pagination-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="supportfitur" class="tab-pane">
            <p class="support-subtitle">
                Temukan fitur opsional yang dapat diaktifkan sesuai kebutuhan bisnis, 
                atau hubungi kami untuk permintaan khusus.
            </p>

            <!-- Daftar Fitur Tambahan -->
            <div class="support-grid">
                <div class="support-card">
                    <h4>🔗 Integrasi Marketplace</h4>
                    <p>Sinkronisasi produk dengan Shopee, Tokopedia, dan marketplace lainnya
                    untuk update stok otomatis.</p>
                </div>
                <div class="support-card">
                    <h4>📊 Laporan Penjualan Lanjutan</h4>
                    <p>Grafik analitik detail: tren produk terlaris, profit harian, dan laporan custom.</p>
                </div>
                <div class="support-card">
                    <h4>💳 Pembayaran Online</h4>
                    <p>Aktifkan metode pembayaran QRIS, e-wallet, dan virtual account.</p>
                </div>
                <div class="support-card">
                    <h4>🤖 AI Rekomendasi</h4>
                    <p>Rekomendasi stok dan harga berbasis data penjualan historis.</p>
                </div>
            </div>

            <!-- Kontak Support -->
            <div class="support-contact">
                <h4>💡 Butuh Fitur Custom?</h4>
                <p>Hubungi tim pengembang untuk request modul khusus:</p>
                <ul>
                    <li>Email: <a href="mailto:support@studioprojectid.com">support@studioprojectid.com</a></li>
                    <li>WhatsApp: <a href="https://wa.me/6285960296108" target="_blank">0859-6029-6108</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function refreshCart(cart) {
    let $tbody = $("#cartTable tbody");
    $tbody.empty();

    $.each(cart, function (id, item) {
        let row = `
            <tr data-id="${id}">
                <td>
                    ${item.name}
                    ${item.variation ? `<br><small class="text-muted">${item.variation}</small>` : ''}
                </td>
                <td><input type="number" value="${item.quantity}" class="form-control qty"></td>
                <td>Rp ${Number(item.subtotal).toLocaleString()}</td>
                <td><button class="btn btn-danger btn-sm remove">X</button></td>
            </tr>
        `;
        $tbody.append(row);
    });
}

// Tambah produk
$(document).on('click', '.btn-add-cart', function(e){
    e.preventDefault();
    let $btn = $(this);
    let productId = $btn.data('id');
    let hasVariation = $btn.data('has-variation') == 1;
    let variations = $btn.data('variations') || [];
    let productPrice = $btn.data('price') || 0;

    if (hasVariation) {
        // 🔹 Popup untuk produk dengan variasi
        let html = `
            <div style="width:100%; max-width:450px; margin:0 auto;">
                <select id="swal_select" class="swal2-select"
                    style="
                        width:100%;
                        padding:10px;
                        border-radius:8px;
                        font-size:14px;
                        max-height:220px;
                        overflow-y:auto;
                    ">
                    <option value="product_${productId}">
                        Produk Utama - Rp ${Number(productPrice).toLocaleString()}
                    </option>`;

        variations.forEach((v) => {
            let optionLabels = (v.options || [])
                .map(opt => opt.value)
                .join(' / ');
            let weightText = v.weight ? ` [ ${parseFloat(v.weight).toLocaleString()} gr ]` : '';
            let label = optionLabels || v.name || '';

            html += `
                <option value="variation_${v.variation_id}">
                    ${label}${weightText} 
                    - Rp ${Number(v.price).toLocaleString()} 
                    (Stok: ${v.stock})
                </option>`;
        });

        html += `
            </select>
            <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; margin-top: 10px;">
                <div style="width:100%; max-width: 450px; marginL0 auto;">
                    <input id="swal_qty" type="number" min="1" value="1"
                        class="swal2-input"
                        style="width: 100%; padding:10px; border-radius:8px; font-size:14px;">
                </div>
            </div>
        `;

        Swal.fire({
            title: 'Pilih produk / varian',
            html: html,
            width: '40%',
            customClass: { popup: 'swal2-responsive-popup' },
            showCancelButton: true,
            confirmButtonText: 'Tambah ke Keranjang',
            preConfirm: () => {
                const val = $('#swal_select').val();
                const qty = parseInt($('#swal_qty').val());

                if (!val) return Swal.showValidationMessage('Pilih produk terlebih dahulu!');
                if (!qty || qty <= 0) return Swal.showValidationMessage('Masukkan jumlah yang valid!');
                
                return { val, qty };
            }
        }).then(result => {
            if (result.isConfirmed && result.value) {
                const { val, qty } = result.value;

                if (val.startsWith('product_')) {
                    const id = val.split('_')[1];
                    $.post(`/umkm/pos/add/${id}`, { 
                        _token: "{{ csrf_token() }}", 
                        qty: qty 
                    }, function (res) {
                        if (res.status === 'success') refreshCart(res.cart);
                        else Swal.fire('Error', res.message || 'Gagal menambah', 'error');
                    });
                } else {
                    const vid = val.split('_')[1];
                    let selectedText = $('#swal_select option:selected').text();

                    $.post(`/umkm/pos/add-variation/${vid}`, { 
                        _token: "{{ csrf_token() }}",
                        qty: qty,
                        variation_label: selectedText
                    }, function (res) {
                        if (res.status === 'success') refreshCart(res.cart);
                        else Swal.fire('Error', res.message || 'Gagal menambah', 'error');
                    });
                }
            }
        });

    } else {
        // 🔹 Produk tanpa variasi
        Swal.fire({
            title: 'Masukkan Jumlah Produk',
            html: `
                <div style="text-align:left;">
                    <label for="swal_qty" style="font-size:14px;">Jumlah (Qty)</label>
                    <input id="swal_qty" type="number" min="1" value="1"
                        style="
                            width:100%;
                            padding:10px;
                            border-radius:8px;
                            border:1px solid #ccc;
                            margin-top:5px;
                            font-size:14px;
                        ">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Tambah ke Keranjang',
            preConfirm: () => {
                const qty = parseInt($('#swal_qty').val()) || 1;
                if (qty < 1) {
                    Swal.showValidationMessage('Jumlah minimal 1!');
                    return false;
                }
                return qty;
            }
        }).then(result => {
            if (result.isConfirmed) {
                const qty = result.value;
                $.post(`/umkm/pos/add/${productId}`, { 
                    _token: "{{ csrf_token() }}",
                    quantity: qty
                }, function(res){
                    if(res.status === 'success') refreshCart(res.cart);
                    else Swal.fire('Error', res.message || 'Gagal menambah', 'error');
                });
            }
        });
    }
});

// Update qty
$(document).on("change", ".qty", function(){
    let row = $(this).closest("tr");
    let id = row.data("id");
    let qty = $(this).val();
    $.post(`/umkm/pos/update/${id}`, {_token:"{{ csrf_token() }}", quantity: qty}, function(res){
        if(res.status === "success"){ refreshCart(res.cart); }
    });
});

// Hapus item
$(document).on("click", ".remove", function(){
    let row = $(this).closest("tr");
    let id = row.data("id");
    $.ajax({
        url: `/umkm/pos/remove/${id}`,
        type: "DELETE",
        data: {_token:"{{ csrf_token() }}"},
        success: function(res){
            if(res.status === "success"){ refreshCart(res.cart); }
        }
    });
});

// Clear cart
$("#clearCart").click(function(){
    $.post(`/umkm/pos/clear`, {_token:"{{ csrf_token() }}"}, function(res){
        if(res.status === "success"){ refreshCart(res.cart); }
    });
});

// Checkout
$("#checkout").click(function(){
    const totalHarga = $("#cartTable tbody tr").toArray().reduce((sum, tr) => {
        const subtotalText = $(tr).find("td:eq(2)").text().replace(/[^\d]/g, "");
        return sum + parseInt(subtotalText || 0);
    }, 0);

    if (totalHarga <= 0) {
        Swal.fire('Keranjang Kosong', 'Tambahkan produk terlebih dahulu sebelum checkout!', 'warning');
        return;
    }

    Swal.fire({
        title: 'Pembayaran',
        html: `
            <div style="text-align:left;">
                <p><strong>Total Belanja:</strong> Rp ${totalHarga.toLocaleString()}</p>
                
                <label for="uangDiterima">Pembayaran:</label>
                <input id="uangDiterima" type="number" min="0" class="swal2-input" placeholder="Masukkan jumlah uang">
                
                <div id="utangSection" style="display:none;margin-top:15px;">
                    <hr>
                    <label for="customerName">Nama Pembeli (utang):</label>
                    <input id="customerName" type="text" class="swal2-input" placeholder="Nama pelanggan">
                    
                    <label for="dueDate">Tanggal Pembayaran:</label>
                    <input id="dueDate" type="date" class="swal2-input">
                </div>

                <p id="kembalianText" style="margin-top:10px;font-weight:bold;font-size:15px;color:green;">
                    Kembalian: Rp 0
                </p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Proses Pembayaran',
        didOpen: () => {
            const uangInput = document.getElementById('uangDiterima');
            const kembalianText = document.getElementById('kembalianText');
            const utangSection = document.getElementById('utangSection');

            uangInput.addEventListener('input', function() {
                const uang = parseInt(this.value || 0);
                const kembali = uang - totalHarga;

                if (kembali < 0) {
                    kembalianText.style.color = 'red';
                    kembalianText.textContent = `Uang kurang Rp ${Math.abs(kembali).toLocaleString()}`;
                    utangSection.style.display = 'block';
                } else {
                    kembalianText.style.color = 'green';
                    kembalianText.textContent = `Kembalian: Rp ${kembali.toLocaleString()}`;
                    utangSection.style.display = 'none';
                }
            });
        },
        preConfirm: () => {
            const uang = parseInt(document.getElementById('uangDiterima').value || 0);
            const kembali = uang - totalHarga;

            if (uang < totalHarga) {
                const nama = document.getElementById('customerName').value.trim();
                const jatuhTempo = document.getElementById('dueDate').value;

                if (!nama) {
                    Swal.showValidationMessage('Nama pelanggan wajib diisi jika uang kurang!');
                    return false;
                }

                if (!jatuhTempo) {
                    Swal.showValidationMessage('Tanggal jatuh tempo wajib diisi!');
                    return false;
                }

                return { uang, customer_name: nama, due_date: jatuhTempo };
            }

            return { uang, customer_name: null, due_date: null };
        }
    }).then(result => {
        if (result.isConfirmed) {
            const data = result.value;
            const uangDiterima = data.uang;
            const kembalian = uangDiterima - totalHarga;

            // Kirim data checkout
            $.post(`/umkm/pos/checkout`, {
                _token: "{{ csrf_token() }}",
                total: totalHarga,
                uang_diterima: uangDiterima,
                kembalian: kembalian,
                customer_name: data.customer_name,
                due_date: data.due_date
            }, function(res){
                if (res.status === "success") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Transaksi Berhasil!',
                        html: `
                            <p><strong>Total:</strong> Rp ${totalHarga.toLocaleString()}</p>
                            <p><strong>Pembayaran:</strong> Rp ${uangDiterima.toLocaleString()}</p>
                            ${res.payment_status === 'partial' ? `
                                <p style="color:red;"><strong>UTANG:</strong> Rp ${(totalHarga - uangDiterima).toLocaleString()}</p>
                                <p><strong>Nama:</strong> ${data.customer_name}</p>
                                <p><strong>Jatuh Tempo:</strong> ${data.due_date}</p>
                            ` : `
                                <p><strong>Kembalian:</strong> Rp ${kembalian.toLocaleString()}</p>
                            `}
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Cetak Struk',
                        cancelButtonText: 'Tutup'
                    }).then(printRes => {
                        if (printRes.isConfirmed) {
                            window.open(`/umkm/pos/receipt/${res.transaction_id}`, '_blank');
                        }
                        refreshCart([]);
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', res.message || 'Checkout gagal', 'error');
                }
            });
        }
    });
});

// Promo toggle
$("#is_promo").change(function(){
    if($(this).is(":checked")){
        $("#promo_section").show();
    } else {
        $("#promo_section").hide();
        $("#promo_price").val(0);
        $("#promo_start").val('');
        $("#promo_end").val('');
    }
});

// Ketika pilih produk
$("#select_product").change(function(){
    let selected = $(this).find(":selected");

    if(selected.val() !== ""){
        $("#product_id").val(selected.val());

        // Set harga dasar & modal
        $("#base_price").val(selected.data("price") || 0);
        $("#cost_price").val(selected.data("cost") || 0);

        // Set harga setelah diskon langsung dari DB
        $("#final_price").val(selected.data("discount") || 0);

        // Promo
        if(selected.data("ispromo") == 1){
            $("#is_promo").prop("checked", true);
            $("#promo_section").show();
            $("#promo_price").val(selected.data("promoprice") || 0);
            $("#promo_start").val(selected.data("promostart") || "");
            $("#promo_end").val(selected.data("promoend") || "");
        } else {
            $("#is_promo").prop("checked", false);
            $("#promo_section").hide();
        }
    } else {
        // Reset kalau tidak pilih produk
        $("#product_id").val("");
        $("#discountForm").trigger("reset");
        $("#promo_section").hide();
    }
});

$(document).on('click', '.adjust-stock', function(e){
    e.preventDefault();
    let $btn  = $(this);
    let id    = $btn.data('id');
    let type  = $btn.data('type');
    let name  = $btn.data('name');

    // Ambil info
    let infoRaw = $btn.attr('data-info') || '{}';
    let info;
    try { info = JSON.parse(infoRaw); } catch (err) { info = {}; }

    // Informasi produk/variasi
    let infoLines = [];
    if(info.type)   infoLines.push(`<strong class="title">${info.type}</strong>`);
    if(info.sku)    infoLines.push(`<span class="label">SKU:</span> <span>${info.sku}</span>`);
    if(info.name)   infoLines.push(`<span class="label">Nama Varian:</span> <span>${info.name}</span>`);
    if(info.options && info.options.length) infoLines.push(`<span class="label">Options:</span> <span>${info.options.join(' / ')}</span>`);
    if(info.weight) infoLines.push(`<span class="label">Berat:</span> <span>${info.weight} gr</span>`);
    if(info.price)  infoLines.push(`<span class="label">Harga:</span> <span>Rp ${Number(info.price).toLocaleString()}</span>`);
    if(info.stock !== undefined) infoLines.push(`<span class="label">Stok Sekarang:</span> <span class="badge">${info.stock}</span>`);

    // HTML Swal
    let html = `
    <div class="swal-container">
        <div class="swal-info-box">
            ${infoLines.map(l => `<div class="swal-row">${l}</div>`).join('')}
        </div>
        <div class="swal-input-box">
            <label class="swal-label">Tambahkan Jumlah Stok</label>
            <input id="swal_stock" type="number" min="1" value="1" class="swal-input">
        </div>
    </div>
    `;

    Swal.fire({
        title: `Tambah Stok`,
        html: html,
        width: 450,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Simpan',
        cancelButtonText: 'Batal',
        focusConfirm: false,
        customClass: {
            popup: 'swal-popup-custom'
        },
        preConfirm: () => {
            const val = parseInt(document.getElementById('swal_stock').value || '0', 10);
            if (!val || val < 1) {
                Swal.showValidationMessage('Masukkan jumlah yang valid (≥ 1)');
                return false;
            }
            return { stock: val };
        }
    }).then(result => {
        if (result.isConfirmed && result.value) {
            $.post(`/umkm/pos/stock/add`, {
                _token: "{{ csrf_token() }}",
                id: id,
                type: type,
                stock: result.value.stock
            }, function(res){
                if(res.status === 'success'){
                    Swal.fire({
                        icon:'success',
                        title:'✅ Berhasil',
                        text: res.message || 'Stok berhasil ditambahkan',
                        timer:1500,
                        showConfirmButton:false
                    });
                    location.reload();
                } else {
                    Swal.fire('Error', res.message || 'Gagal menambahkan stok', 'error');
                }
            }).fail(function(){
                Swal.fire('Error', 'Terjadi kesalahan server', 'error');
            });
        }
    });
});

function paginateTransactions() {
    const tbody = document.getElementById('transaksiBody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const rowsPerPage = 10;
    const pagination = document.getElementById('transaksiPagination');
    const info = document.getElementById('transaksiInfo');
    let currentPage = 1;

    function displayPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        rows.forEach((r, i) => r.style.display = (i >= start && i < end) ? '' : 'none');

        info.textContent = `Menampilkan ${Math.min(start+1, rows.length)} - ${Math.min(end, rows.length)} dari ${rows.length} transaksi`;
    }

    function setupPagination() {
        pagination.innerHTML = '';
        const totalPages = Math.ceil(rows.length / rowsPerPage);
        for(let i=1; i<=totalPages; i++){
            const btn = document.createElement('button');
            btn.textContent = i;
            if(i===currentPage) btn.classList.add('active');
            btn.addEventListener('click', ()=>{
                currentPage = i;
                displayPage(currentPage);
                setupPagination();
            });
            pagination.appendChild(btn);
        }
    }

    displayPage(currentPage);
    setupPagination();
}

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.querySelector('#tableTransaksi tbody');
    const refreshBtn = document.getElementById('refreshTransaksi');
    const filterStart = document.getElementById('filterStart');
    const filterEnd = document.getElementById('filterEnd');
    const btnFilter = document.getElementById('btnFilter');

    function loadTransactions(start = null, end = null) {
        let url = '/api/stock/transactions';
        if(start && end){
            url += `?start=${start}&end=${end}`;
        }

        fetch(url)
            .then(r => r.json())
            .then(data => {
                tableBody.innerHTML = '';
                data.forEach(t => {
                    // jika ada varian → tampilkan nama & beratnya
                    const variationInfo = t.variation_name 
                        ? `${t.variation_name}${t.weight ? ' (' + t.weight + ' g)' : ''}`
                        : '-';

                    tableBody.innerHTML += `
                        <tr>
                            <td>${t.created_at}</td>
                            <td>${t.product_name}</td>
                            <td>${variationInfo}</td>
                            <td>${t.type}</td>
                            <td>${t.quantity}</td>
                            <td>${t.supplier ?? ''}</td>
                            <td>${t.note ?? ''}</td>
                            <td>${t.user}</td>
                        </tr>`;
                });
                paginateTransactions(); // panggil pagination
            });
    }

    // tombol refresh tetap memanggil dengan range saat ini
    refreshBtn.addEventListener('click', () => loadTransactions(filterStart.value, filterEnd.value));

    // tombol filter memanggil dengan range yang dipilih
    btnFilter.addEventListener('click', () => loadTransactions(filterStart.value, filterEnd.value));

    // load default saat halaman dibuka (misal bulan ini)
    loadTransactions(filterStart.value, filterEnd.value);

    document.getElementById('formUpdateStock').addEventListener('submit', function(e){
        e.preventDefault();

        const formData = new FormData(this);

        fetch('/api/stock/update', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Transaksi berhasil!',
                    text: `Stok baru: ${res.new_stock}`,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: res.message || 'Terjadi kesalahan.',
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan pada server.'
            });
        });
    });

});

</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function loadStockSummary() {
        fetch('/stock/summary')
            .then(res => res.json())
            .then(data => {
                document.getElementById('totalProduk').textContent = data.totalProduk;
                document.getElementById('totalStok').textContent   = data.totalStok;
                document.getElementById('totalTransaksi').textContent = data.totalTransaksi;
            })
            .catch(err => console.error('Gagal memuat ringkasan stok', err));
    }

    // Panggil saat halaman pertama kali dibuka
    loadStockSummary();

    // Optional: auto-refresh setiap 30 detik
    setInterval(loadStockSummary, 30000);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('laporanProdukBody'); // tbody untuk tabel
    const paginationContainer = document.getElementById('laporanProdukPagination');
    const infoContainer = document.getElementById('laporanProdukInfo');
    const perPage = 10;
    let reportData = [];
    let currentPage = 1;
    let totalPages = 1;

    async function loadProductReport() {
        const start = document.getElementById('filterStart').value;
        const end   = document.getElementById('filterEnd').value;

        try {
            const res = await fetch(`/api/report/products?start=${start}&end=${end}`);
            const data = await res.json();

            // Update ringkasan
            document.getElementById('bestProduct').textContent = data.bestProduct;
            document.getElementById('totalSales').textContent = data.totalSales;
            document.getElementById('totalStockMovement').textContent = data.totalStockMovement;

            reportData = data.details;
            totalPages = Math.ceil(reportData.length / perPage);
            renderPage(1);
            renderPagination();

            // Chart
            const ctx = document.getElementById('chartPenjualan').getContext('2d');
            if(window.salesChart) window.salesChart.destroy();
            window.salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.chart.map(c => c.tanggal),
                    datasets: [{
                        label: 'Jumlah Terjual',
                        data: data.chart.map(c => c.total),
                        borderColor: '#4caf50',
                        fill: false
                    }]
                }
            });

        } catch (error) {
            console.error('Gagal load laporan produk', error);
        }
    }

    function renderPage(page) {
        currentPage = page;
        const start = (page - 1) * perPage;
        const end = start + perPage;
        const pageData = reportData.slice(start, end);

        tableBody.innerHTML = '';
        pageData.forEach(d => {
            tableBody.innerHTML += `
                <tr>
                    <td>${d.type}</td>
                    <td>${d.name}</td>
                    <td>${d.sold}</td>
                    <td>${d.stock_start}</td>
                    <td>${d.stock_end}</td>
                    <td>${d.masuk}</td>
                    <td>${d.keluar}</td>
                    <td>${d.adjust}</td>
                    <td>Rp ${new Intl.NumberFormat('id-ID').format(d.revenue)}</td>
                </tr>
            `;
        });

        // Update info footer
        const startItem = reportData.length ? start + 1 : 0;
        const endItem = Math.min(end, reportData.length);
        infoContainer.textContent = `Menampilkan ${startItem}–${endItem} dari ${reportData.length} data`;
    }

    function renderPagination() {
        paginationContainer.innerHTML = '';
        if(totalPages <= 1) return;

        for(let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.classList.add('page-btn');
            if(i === currentPage) btn.classList.add('active');
            btn.addEventListener('click', () => renderPage(i));
            paginationContainer.appendChild(btn);
        }
    }

    document.getElementById('btnFilter').addEventListener('click', loadProductReport);
    loadProductReport();
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rowsPerPage = 10; // tampil 10 data per halaman
    const tableBody   = document.getElementById('stockTableBody');
    const rows        = Array.from(tableBody.querySelectorAll('tr'));
    const pagination  = document.getElementById('tablePagination');
    const infoBox     = document.getElementById('tableInfo');
    const totalRows   = rows.length;
    const totalPages  = Math.ceil(totalRows / rowsPerPage);

    function renderPage(page) {
        // sembunyikan semua row
        rows.forEach(r => r.style.display = 'none');
        // tampilkan sesuai halaman
        const start = (page - 1) * rowsPerPage;
        const end   = Math.min(start + rowsPerPage, totalRows);
        rows.slice(start, end).forEach(r => r.style.display = '');

        // update tombol aktif
        Array.from(pagination.children).forEach((btn, i) => {
            btn.classList.toggle('active', i + 1 === page);
        });

        // update info total
        infoBox.textContent = `Menampilkan ${start + 1}–${end} dari ${totalRows} data`;
    }

    function createPagination() {
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.addEventListener('click', () => renderPage(i));
            pagination.appendChild(btn);
        }
    }

    if (totalRows > rowsPerPage) {
        createPagination();
        renderPage(1);
    } else {
        // Jika data <= 10, tetap tampilkan info total
        infoBox.textContent = `Menampilkan 1–${totalRows} dari ${totalRows} data`;
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const input   = document.getElementById('searchStock');
    const rows    = document.querySelectorAll('#stockTableBody tr');

    input.addEventListener('input', function(){
        const q = this.value.trim().toLowerCase();

        rows.forEach(row => {
            // Ambil semua teks dari setiap kolom
            const text = row.innerText.toLowerCase();

            // Tampilkan baris hanya jika cocok
            row.style.display = text.includes(q) ? '' : 'none';
        });
    });
});
</script>
<script>
    const productsData = @json($productsForJs);
    let filteredProducts = [...productsData]; // untuk filter
    const perPage = 5;
    let currentPage = 1;

    function renderTable(page = 1) {
    const tbody = $("#productsTableBody");
    tbody.empty();

    const start = (page - 1) * perPage;
    const end = start + perPage;
    const pageProducts = filteredProducts.slice(start, end);

    pageProducts.forEach((product, index) => {
        // 🧩 Render variasi produk
        let variationHtml = '';
        if (product.variations && product.variations.length > 0) {
            variationHtml = '<ul class="mb-0 ps-3">';
            product.variations.forEach((v, vi) => {
                variationHtml += `
                    <li>
                        ${vi + 1}. (Stok: ${v.stock ?? 0}, Rp ${Number(v.price).toLocaleString()}, ${v.weight ?? 0} gr)
                        ${v.options && v.options.length > 0 ? `
                            <br>
                            <small class="text-muted">
                                [${v.options.map(o => `${o.attribute}: ${o.value}`).join(', ')}]
                            </small>
                        ` : ''}
                    </li>
                `;
            });
            variationHtml += '</ul>';
        }

        // 🏷️ Logika harga dan promo
        let priceHtml = '';
        const now = new Date();
        const promoStart = product.promo_start ? new Date(product.promo_start) : null;
        const promoEnd = product.promo_end ? new Date(product.promo_end) : null;
        const isPromoActive = product.is_promo && promoStart && promoEnd && now >= promoStart && now <= promoEnd;

        if (isPromoActive) {
            priceHtml = `
                <span class="text-success fw-bold">
                    Rp ${Number(product.final_price ?? product.promo_price).toLocaleString()}
                </span><br>
                <small class="text-muted">
                    <del>Rp ${Number(product.price).toLocaleString()}</del><br>
                    Promo: ${formatDate(product.promo_start)} - ${formatDate(product.promo_end)}
                </small>
            `;
        } else {
            priceHtml = `Rp ${Number(product.final_price ?? product.price).toLocaleString()}`;
        }

        // 🧾 Render kategori
        const categoryName = product.category && product.category.name ? product.category.name : '-';

        // 🧰 Append baris produk ke tabel
        tbody.append(`
            <tr>
                <td>${start + index + 1}</td>
                <td>
                    ${product.thumbnail ? `<img src="/${product.thumbnail}" alt="${product.name}" width="50">` : '-'}
                </td>
                <td>
                    ${product.name}
                    ${variationHtml}
                </td>
                <td>${categoryName}</td>
                <td>${product.sku ?? '-'}</td>
                <td>${priceHtml}</td>
                <td>${product.stockProduct ?? 0}</td>
                <td>
                    <button class="btn btn-sm btn-success btn-add-cart"
                        data-id="${product.id}"
                        data-price="${product.price}"
                        data-final-price="${product.final_price ?? product.price}"
                        data-has-variation="${product.variations && product.variations.length > 0 ? 1 : 0}"
                        data-variations='${JSON.stringify(product.variations)}'>
                        + Keranjang
                    </button>
                </td>
            </tr>
        `);
    });

    renderPagination(Math.ceil(filteredProducts.length / perPage));
    renderTableInfo(start, pageProducts.length, filteredProducts.length);
}

// 🔹 Fungsi bantu untuk format tanggal promo
function formatDate(dateString) {
    if (!dateString) return '';
    const d = new Date(dateString);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

// 🔹 Fungsi bantu format tanggal ke format Indonesia (misal: 20 Okt 2025)
function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}


    function renderPagination(totalPages) {
        const container = $("#tablePaginationProduct");
        container.empty();
        for(let i = 1; i <= totalPages; i++){
            container.append(`<button class="btn btn-sm btn-pagination ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`);
        }
    }

    function renderTableInfo(startIndex, countOnPage, total) {
        const info = `Menampilkan ${startIndex + 1}-${startIndex + countOnPage} dari ${total} data`;
        $("#tableInfo").text(info);
    }

    // Pagination click
    $(document).on('click', '.btn-pagination', function() {
        currentPage = parseInt($(this).data('page'));
        renderTable(currentPage);
    });

    // Filter pencarian
    $("#productSearch").on('keyup', function() {
        const query = $(this).val().toLowerCase();
        filteredProducts = productsData.filter(p => p.name.toLowerCase().includes(query));
        currentPage = 1; // reset ke halaman 1
        renderTable(currentPage);
    });

    // Init
    renderTable();
</script>

@endsection