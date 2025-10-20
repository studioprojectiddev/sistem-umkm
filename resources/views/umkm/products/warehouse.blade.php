@extends('layouts.app')

@section('title', 'Warehouse')

@section('content')
<h1 class="title">📦 Warehouse Management</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.product.warehouse') }}" class="active">Warehouse</a></li>
</ul>

<style>
.swal-label {
    font-weight: 600;
    font-size: 14px;
    color: #374151;
    display: block;
    margin-bottom: 4px;
}
.swal2-popup {
    border-radius: 12px !important;
    padding: 25px 30px !important;
}
.swal2-select, .swal2-input {
    border-radius: 8px !important;
    border: 1px solid #d1d5db !important;
    padding: 8px 10px !important;
    font-size: 14px !important;
}
.swal2-select:focus, .swal2-input:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 2px rgba(37,99,235,0.2);
}
.swal2-confirm {
    font-weight: 600;
}
.swal2-cancel {
    font-weight: 500;
}
/* === TAB NAVIGATION === */
.tab-container {
    margin-top: 1.5rem;
}
.tab-buttons {
    display: flex;
    gap: 0.5rem;
    border-bottom: 2px solid #eee;
    margin-bottom: 1rem;
}
.tab-button {
    padding: 0.6rem 1rem;
    border: none;
    background: #f5f5f5;
    cursor: pointer;
    border-radius: 8px 8px 0 0;
    font-weight: 600;
    color: #555;
    transition: all 0.2s ease;
}
.tab-button.active {
    background: #fff;
    color: #222;
    box-shadow: 0 -2px 6px rgba(0,0,0,0.05);
}
.tab-content {
    display: none;
    background: #fff;
    border-radius: 0 0 12px 12px;
    padding: 1.2rem;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
}
.tab-content.active {
    display: block;
}

/* === TABLE STYLE === */
.table-container {
    overflow-x: auto;
}
.custom-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    margin-top: 1rem;
}
.custom-table th, .custom-table td {
    padding: 10px;
    border: 1px solid #e0e0e0;
    text-align: left;
}
.custom-table th {
    background: #f3f4f6;
    font-weight: 600;
}
.custom-table tr:hover {
    background: #fafafa;
}
.low-stock {
    background-color: #fff2f2;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 15px;
    gap: 10px;
}
.pagination button {
    padding: 6px 12px;
    background-color: #4f46e5;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
}
.pagination button:hover {
    background-color: #4338ca;
}
.pagination button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}
.page-info {
    font-size: 14px;
    color: #555;
}

/* === WAREHOUSE CARD === */
.warehouse-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}
.warehouse-card {
    background: #fff;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}
.warehouse-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 14px rgba(0,0,0,0.12);
}
.warehouse-card h4 {
    margin: 0;
    color: #333;
    font-size: 1.1rem;
}
.warehouse-card p {
    margin: 4px 0;
    color: #666;
    font-size: 0.85rem;
}
.warehouse-card small {
    color: #999;
}

/* === BADGES === */
.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}
.badge-danger {
    background: #ff4b4b;
    color: #fff;
}
.badge-warning {
    background: #ffc107;
    color: #000;
}
/* ===============================
   Basic Layout
================================= */
.d-flex { display: flex; }
.justify-between { justify-content: space-between; }
.align-center { align-items: center; }
.mb-2 { margin-bottom: 1rem; }

.tab-button {
    background: #4e73df;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.3s;
}
.tab-button:hover { background: #2e59d9; }

.text-muted { color: #888; }

.warehouse-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}
.warehouse-card {
    background: #fff;
    border: 1px solid #eee;
    padding: 1rem;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: 0.3s;
}
.warehouse-card:hover { box-shadow: 0 4px 10px rgba(0,0,0,0.1); }

/* ===============================
   Modal Styling
================================= */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
    animation: fadeInBg 0.3s ease;
}

.modal-content {
    background: #fff;
    padding: 1.8rem;
    border-radius: 14px;
    width: 500px;
    max-width: 90%;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    animation: slideUp 0.3s ease;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #f0f0f0;
    margin-bottom: 1rem;
}

.modal-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #4e73df;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.4rem;
  cursor: pointer;
  color: #555;
}

.modal-body {
  padding: 1.2rem 1.5rem;
}

.modal-body .form-label {
  font-weight: 600;
  display: block;
  margin-bottom: 4px;
}

.modal-body .form-select,
.modal-body .form-control {
  width: 100%;
  padding: 0.55rem 0.75rem;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: border-color 0.2s ease;
}

.modal-body .form-select:focus,
.modal-body .form-control:focus {
  border-color: #3b82f6;
  outline: none;
}

.modal-body .row {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.modal-body .col-md-6 {
  flex: 1 1 calc(50% - 1rem);
  min-width: 260px;
}

.close-btn {
    cursor: pointer;
    font-size: 22px;
    color: #777;
    transition: color 0.2s ease;
}
.close-btn:hover { color: #000; }

.modal-footer {
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  border-top: 1px solid #eee;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}
.form-group label {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}
.form-group input,
.form-group textarea {
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 14px;
    transition: border-color 0.2s;
}
.form-group input:focus,
.form-group textarea:focus {
    border-color: #4e73df;
    outline: none;
}

.full-width {
    grid-column: 1 / -1;
}

.required {
    color: red;
}

.form-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.6rem;
    margin-top: 1rem;
}

.search-bar {
    display: flex;
    justify-content: flex-end;
}
.search-bar input {
    width: 300px;
    border-radius: 8px;
    padding: 8px 10px;
    border: 1px solid #ccc;
}
.filter-bar select,
.filter-bar input {
    min-width: 180px;
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.filter-bar {
    display: flex;
    justify-content: flex-start;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-primary {
    background: #4e73df;
    color: #fff;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
}
.btn-primary:hover { background: #2e59d9; }

.btn-secondary {
    background: #e0e0e0;
    color: #333;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
}
.btn-secondary:hover { background: #ccc; }

@keyframes fadeInBg {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* === RESPONSIVE === */
@media (max-width: 600px) {
    .tab-buttons {
        flex-direction: column;
    }
    .tab-button {
        border-radius: 8px;
    }
}
</style>

<div class="tab-container">
    {{-- === TAB BUTTONS === --}}
    <div class="tab-buttons">
        <button class="tab-button active" data-tab="warehouses">🏭 Daftar Gudang</button>
        <button class="tab-button" data-tab="stock">📊 Manajemen Stok</button>
        <button class="tab-button" data-tab="transfer">🔁 Transfer Stok</button>
    </div>

    {{-- === TAB 1: GUDANG === --}}
    <div class="tab-content active" id="warehouses">
        <div class="d-flex justify-between align-center mb-2">
            <h3>🏭 Daftar Gudang</h3>
            <button class="btn btn-primary btn-add-warehouse">+ Tambah Gudang</button>
        </div>

        @if($warehouses->isEmpty())
            <p class="text-muted">Belum ada data gudang.</p>
        @else
        <div class="warehouse-grid">
            @foreach($warehouses as $w)
                <div class="warehouse-card">
                    <h4>{{ $w->name }}</h4>
                    <p><strong>Kota:</strong> {{ $w->city ?? '-' }}</p>
                    <p><strong>PIC:</strong> {{ $w->pic_name ?? '-' }}</p>
                    <p><strong>Telepon:</strong> {{ $w->phone ?? '-' }}</p>
                    <small>{{ $w->address }}</small>
                </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- === TAB 2: Manajemen Stok === --}}
    <div class="tab-content" id="stock">
        <div class="d-flex justify-between align-center mb-2">
            <h3>📊 Manajemen Stok Gudang</h3>
        </div>

        {{-- 🔍 Input Pencarian Produk --}}
        <div class="search-bar mb-3">
            <input type="text" id="searchProduct" class="form-control" placeholder="Cari produk atau variasi...">
        </div>

        @if($nonVariationProducts->isEmpty() && $variationProducts->isEmpty())
            <p class="text-muted">Belum ada data produk untuk manajemen stok.</p>
        @else
            <div class="table-container">
                <table class="custom-table" id="stockTable">
                    <thead>
                        <tr>
                            <th>Produk & Variasi</th>
                            <th>Gudang</th>
                            <th>Stok Saat Ini</th>
                            <th>Min. Stok</th>
                            <th>Posisi Rak</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Produk tanpa variasi (per warehouse) --}}
                        @foreach ($nonVariationProducts as $item)
                            <tr class="clickable-row"
                                data-product-id="{{ $item->product_id }}"
                                data-variation-id=""
                                data-warehouse-id="{{ $item->warehouse_id }}"
                                data-product-name="{{ $item->product_name }}"
                                data-min-stock="{{ $item->min_stock ?? 0 }}"
                                data-rack-position="{{ $item->rack_position ?? '' }}"
                                data-current-stock="{{ $item->stock ?? 0 }}"
                                data-adjusted-stock="{{ $item->adjusted_stock ?? $item->stock ?? 0 }}">
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->warehouse_name ?? '-' }}</td>
                                <td>{{ $item->adjusted_stock ?? $item->stock ?? 0 }}</td>
                                <td>{{ $item->min_stock ?? 0 }}</td>
                                <td>{{ $item->rack_position ?? '-' }}</td>
                            </tr>
                        @endforeach

                        {{-- Produk variasi (per warehouse) --}}
                        @foreach ($variationProducts as $item)
                            <tr class="clickable-row"
                                data-product-id="{{ $item->product_id }}"
                                data-variation-id="{{ $item->variation_id }}"
                                data-warehouse-id="{{ $item->warehouse_id }}"
                                data-product-name="{{ $item->product_name }}"
                                data-min-stock="{{ $item->min_stock ?? 0 }}"
                                data-rack-position="{{ $item->rack_position ?? '' }}"
                                data-current-stock="{{ $item->stock ?? 0 }}"
                                data-adjusted-stock="{{ $item->adjusted_stock ?? $item->stock ?? 0 }}">
                                <td>{{ $item->product_name }} ({{ $item->variation_name }})</td>
                                <td>{{ $item->warehouse_name ?? '-' }}</td>
                                <td>{{ $item->adjusted_stock ?? $item->stock ?? 0 }}</td>
                                <td>{{ $item->min_stock ?? 0 }}</td>
                                <td>{{ $item->rack_position ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <button id="prevPage">Prev</button>
                <span class="page-info" id="pageInfo"></span>
                <button id="nextPage">Next</button>
            </div>
        @endif
    </div>

    {{-- === TAB 3: Transfer Stok Antar Gudang === --}}
    <div class="tab-content" id="transfer">
        <div class="d-flex justify-between align-center mb-3">
            <h3>🔁 Transfer Stok Antar Gudang</h3>
        </div>

        {{-- 🔍 Filter Produk --}}
        <div class="filter-bar mb-3">
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" id="searchTransferProduct" class="form-control" placeholder="Cari produk atau variasi...">
                <!-- <select id="filterStatus" class="form-select" style="margin-left:10px;">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="in_transit">Sedang Dikirim</option>
                    <option value="completed">Selesai</option>
                </select> -->
                <select id="filterFromWarehouse" class="form-select" style="margin-left:10px;">
                    <option value="">Gudang Asal</option>
                    @foreach ($warehouses as $w)
                        <option value="{{ strtolower($w->name) }}">{{ $w->name }}</option>
                    @endforeach
                </select>
                <select id="filterToWarehouse" class="form-select" style="margin-left:10px;">
                    <option value="">Gudang Tujuan</option>
                    @foreach ($warehouses as $w)
                        <option value="{{ strtolower($w->name) }}">{{ $w->name }}</option>
                    @endforeach
                </select>
                <button style="margin-left:10px;" type="button" class="btn btn-primary open-transfer">+ Transfer Baru</button>
            </div>
        </div>

        <div class="card mt-3" style="margin-top:10px;">
            <div class="card-body table-responsive">
                <table class="table table-bordered" id="transferTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produk</th>
                            <th>Gudang Asal</th>
                            <th>Gudang Tujuan</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transfers as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{ $item->product_name }}
                                    @if($item->variation_name)
                                        ({{ $item->variation_name }})
                                    @endif
                                </td>
                                <td>{{ $item->from_warehouse_name }}</td>
                                <td>{{ $item->to_warehouse_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>
                                    <span class="badge 
                                        @if($item->status == 'pending') bg-warning 
                                        @elseif($item->status == 'in_transit') bg-info 
                                        @else bg-success @endif">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item->created_at)->setTimezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">
                                    Belum ada data transfer stok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 🔢 Navigasi pagination manual --}}
            <div class="pagination d-flex justify-content-between align-items-center mt-3">
                <button class="btn btn-light" id="prevTransferPage">Sebelumnya</button>
                <span id="transferPageInfo" class="text-muted">Halaman 1</span>
                <button class="btn btn-light" id="nextTransferPage">Berikutnya</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Gudang -->
<div id="warehouseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-warehouse"></i> Tambah Gudang Baru</h3>
            <button style="top:20px;right:30px;" type="button" class="btn-close close-btn" aria-label="Close">x</button>
        </div>

        <div class="modal-body">
            <form id="warehouseForm" method="POST" action="{{ route('umkm.warehouse.store') }}">
                @csrf
                <div class="form-grid">
    
                    <div class="form-group">
                        <label>Tipe <span class="required">*</span></label>
                        <select name="type" class="form-select" id="type" required>
                            <option value="store">Toko</option>
                            <option value="warehouse">Gudang</option>
                        </select>
                    </div>
    
                    <div class="form-group">
                        <label>Nama<span class="required">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Gudang Utama">
                    </div>
    
                    <div class="form-group">
                        <label>Kode<span class="required">*</span></label>
                        <input type="text" name="code" required placeholder="Contoh: GUD001">
                    </div>
    
                    <div class="form-group">
                        <label>Kota</label>
                        <input type="text" name="city" placeholder="Contoh: Jakarta">
                    </div>
    
                    <div class="form-group full-width">
                        <label>Alamat Lengkap</label>
                        <textarea name="address" rows="2" placeholder="Masukkan alamat lengkap"></textarea>
                    </div>
    
                    <div class="form-group">
                        <label>Nama PIC</label>
                        <input type="text" name="pic_name" placeholder="Nama penanggung jawab">
                    </div>
    
                    <div class="form-group">
                        <label>Kontak PIC</label>
                        <input type="text" name="pic_contact" placeholder="No HP / Email PIC">
                    </div>
    
                    <div class="form-group">
                        <label>Nomor Telepon Gudang</label>
                        <input type="text" name="phone" placeholder="Contoh: 08123456789">
                    </div>
                </div>
    
                <div class="form-footer">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="stockModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>📦 Update Stok Gudang</h3>
            <button style="top:20px;right:30px;" type="button" class="btn-close close-btn" aria-label="Close">x</button>
        </div>

        <div class="modal-body">
            <form id="stockForm">
                @csrf
                <input type="hidden" name="product_id" id="product_id">
                <input type="hidden" name="variation_id" id="variation_id">

                <div class="form-group">
                    <!-- <label>Produk</label> -->
                    <input type="hidden" id="product_name_display" disabled>
                </div>

                <div class="form-group">
                    <label for="warehouse_id">Pilih Gudang</label>
                    <select class="form-select" name="warehouse_id" id="warehouse_id" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }} — {{ $w->city }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="action_type">Jenis Aksi</label>
                    <select class="form-select" name="action_type" id="action_type" required>
                        <option value="add">Tambah Stok</option>
                        <option value="reduce">Kurangi Stok</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity">Jumlah Stok</label>
                    <input type="number" name="quantity" id="quantity" min="1" required placeholder="Masukkan jumlah stok">
                </div>

                <div class="form-group">
                    <label for="min_stock">Stok Minimum</label>
                    <input type="number" name="min_stock" id="min_stock" min="0" placeholder="Misal: 10">
                </div>

                <div class="form-group">
                    <label for="rack_position">Posisi Rak</label>
                    <input type="text" name="rack_position" id="rack_position" maxlength="100" placeholder="Misal: Rak A3 / Baris 2">
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                    <!-- <button type="button" class="btn-close close-btn">Tutup</button> -->
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="transferModal">
  <div class="modal-dialog">
    <form id="transferForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Transfer Stok</h5>
        <button type="button" class="btn-close close-transfer" aria-label="Close">×</button>
      </div>

      <div class="modal-body">
        @csrf
        <div class="row g-3">
          
          {{-- Gudang Asal --}}
          <div class="col-md-6">
            <label class="form-label">Gudang Asal</label>
            <select name="from_warehouse_id" class="form-select" required>
              <option value="">-- Pilih Gudang Asal --</option>
              @foreach ($warehouses as $w)
                <option value="{{ $w->id }}">{{ $w->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- Gudang Tujuan --}}
          <div class="col-md-6">
            <label class="form-label">Gudang Tujuan</label>
            <select name="to_warehouse_id" class="form-select" required>
              <option value="">-- Pilih Gudang Tujuan --</option>
              @foreach ($warehouses as $w)
                <option value="{{ $w->id }}">{{ $w->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- Produk --}}
          <div class="col-md-6">
            <label class="form-label">Produk</label>
            <select name="product_id" id="productSelect" class="form-select" required>
              <option value="">-- Pilih Produk --</option>
              @foreach ($nonVariationProducts as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- Variasi --}}
          <div class="col-md-6" id="variationWrapper" style="display: none;">
            <label class="form-label">Variasi Produk</label>
            <select name="variation_id" id="variationSelect" class="form-select">
              <option value="">-- Tidak Ada Variasi --</option>
            </select>
          </div>

          {{-- Jumlah --}}
          <div class="col-md-6">
            <label class="form-label">Jumlah Transfer</label>
            <input type="number" name="quantity" class="form-control" min="1" required>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary close-transfer">Tutup</button>
        <button type="submit" class="btn btn-primary">Kirim Transfer</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    /* ======================= 🔄 TAB SWITCH (single source) ======================= */
    const tabButtons = document.querySelectorAll('.tab-buttons .tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    function activateTab(tabId, skipSave = false) {
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));

        const targetBtn = document.querySelector(`.tab-button[data-tab="${tabId}"]`);
        const targetContent = document.getElementById(tabId);

        if (targetBtn && targetContent) {
            targetBtn.classList.add('active');
            targetContent.classList.add('active');
            if (!skipSave) sessionStorage.setItem('activeTab', tabId);
        }
    }

    // Selalu mulai dari tab pertama saat halaman baru dimuat
    let savedTab = sessionStorage.getItem('activeTab');

    // Jika tidak ada tab tersimpan, pakai tab pertama (warehouses)
    if (!savedTab || performance.navigation.type === 1) {
        // performance.navigation.type === 1 artinya user melakukan reload baru
        savedTab = 'warehouses';
        sessionStorage.setItem('activeTab', savedTab);
    }

    activateTab(savedTab, true);

    // Klik tab manual
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.dataset.tab;
            activateTab(tabId);
        });
    });

    /* ======================= 🏢 MODAL TAMBAH / EDIT GUDANG ======================= */
    const warehouseModal = document.getElementById('warehouseModal');
    const openWarehouseBtn = document.querySelector('.btn-add-warehouse');
    const warehouseForm = document.getElementById('warehouseForm');
    const modalTitle = warehouseModal?.querySelector('.modal-header h3');
    let currentMode = "create";
    let currentWarehouseId = null;

    if (openWarehouseBtn && warehouseModal && warehouseForm) {
        openWarehouseBtn.addEventListener('click', () => {
            currentMode = "create";
            currentWarehouseId = null;
            if (modalTitle) modalTitle.innerHTML = '<i class="fas fa-warehouse"></i> Tambah Gudang Baru';
            warehouseForm.action = "{{ route('umkm.warehouse.store') }}";
            warehouseForm.reset();
            document.getElementById("deleteWarehouseBtn")?.classList.add("hidden");
            warehouseModal.style.display = 'flex';
        });
    }

    if (warehouseModal) {
        const closeWarehouseBtns = warehouseModal.querySelectorAll('.close-btn');
        closeWarehouseBtns.forEach(btn => btn.addEventListener('click', () => warehouseModal.style.display = 'none'));
        window.addEventListener('click', e => { if (e.target === warehouseModal) warehouseModal.style.display = 'none'; });
    }

    // Edit gudang dari card
    document.querySelectorAll('.warehouse-card').forEach(card => {
        card.addEventListener('click', () => {
            if (!warehouseModal || !warehouseForm) return;
            currentMode = "edit";
            currentWarehouseId = card.dataset.id;
            if (modalTitle) modalTitle.innerHTML = '<i class="fas fa-edit"></i> Edit Gudang';
            warehouseForm.action = `/umkm/warehouse/update/${currentWarehouseId}`;

            warehouseForm.querySelector('[name="name"]').value = card.querySelector('h4')?.textContent.trim() || '';
            warehouseForm.querySelector('[name="city"]').value = card.dataset.city || '';
            warehouseForm.querySelector('[name="address"]').value = card.dataset.address || '';
            warehouseForm.querySelector('[name="pic_name"]').value = card.dataset.pic_name || '';
            warehouseForm.querySelector('[name="pic_contact"]').value = card.dataset.pic_contact || '';
            warehouseForm.querySelector('[name="phone"]').value = card.dataset.phone || '';
            warehouseForm.querySelector('[name="code"]').value = card.dataset.code || '';

            document.getElementById("deleteWarehouseBtn")?.classList.remove("hidden");
            warehouseModal.style.display = 'flex';
        });
    });

    /* ======================= 🗑️ HAPUS & SIMPAN GUDANG ======================= */
    if (warehouseForm) {
        // Create delete button (only once)
        if (!document.getElementById('deleteWarehouseBtn')) {
            const deleteBtn = document.createElement('button');
            deleteBtn.id = "deleteWarehouseBtn";
            deleteBtn.type = "button";
            deleteBtn.className = "btn-danger hidden";
            deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Hapus';
            warehouseForm.querySelector('.form-footer')?.appendChild(deleteBtn);

            deleteBtn.addEventListener('click', () => {
                if (!currentWarehouseId) return;
                Swal.fire({
                    title: 'Hapus Gudang?',
                    text: 'Data gudang akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch(`/umkm/warehouse/delete/${currentWarehouseId}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }
                        })
                        .then(async res => {
                            let data;
                            try { data = await res.json(); }
                            catch { data = { success: false, message: 'Respons server tidak valid.' }; }

                            Swal.fire({
                                icon: data.success ? 'success' : 'error',
                                title: data.success ? 'Berhasil' : 'Gagal',
                                text: data.message || 'Operasi selesai.'
                            });
                            if (data.success) location.reload();
                        })
                        .catch(() => Swal.fire('Error', 'Gagal menghapus gudang.', 'error'));
                    }
                });
            });
        }

        warehouseForm.addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(warehouseForm);
            const url = warehouseForm.action;

            fetch(url, {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                let data;
                try { data = await res.json(); }
                catch { data = { success: false, message: 'Respons server tidak valid.' }; }

                Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? 'Berhasil' : 'Gagal',
                    text: data.message || 'Terjadi kesalahan.'
                });

                if (data.success) {
                    warehouseModal.style.display = 'none';
                    warehouseForm.reset();
                    setTimeout(() => location.reload(), 1000);
                }
            })
            .catch(() => Swal.fire('Error', 'Gagal mengirim data ke server.', 'error'));
        });
    }

    /* ======================= 📦 UPDATE STOK ======================= */
    const stockModal = document.getElementById('stockModal');
    const stockForm = document.getElementById('stockForm');
    if (stockModal && stockForm) {
        const closeStockBtns = stockModal.querySelectorAll('.close-btn');
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', () => {
                stockModal.style.display = 'flex';
                document.getElementById('product_id').value = row.dataset.productId || '';
                document.getElementById('variation_id').value = row.dataset.variationId || '';
                document.getElementById('warehouse_id').value = row.dataset.warehouseId || '';
                document.getElementById('min_stock').value = row.dataset.minStock || 0;
                document.getElementById('rack_position').value = row.dataset.rackPosition || '';
                document.getElementById('quantity').value = '';
                document.getElementById('action_type').value = 'add';
                const nameDisplay = stockModal.querySelector('.modal-product-name');
                if (nameDisplay) nameDisplay.textContent = row.dataset.productName || '';
            });
        });
        closeStockBtns.forEach(btn => btn.addEventListener('click', () => stockModal.style.display = 'none'));
        window.addEventListener('click', e => { if (e.target === stockModal) stockModal.style.display = 'none'; });

        stockForm.addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(stockForm);
            const payload = Object.fromEntries(formData.entries());

            if (!payload.product_id || !payload.warehouse_id || !payload.quantity) {
                Swal.fire('Oops!', 'Harap isi semua kolom yang wajib diisi.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Perbarui Stok?',
                text: `Apakah Anda yakin ingin ${payload.action_type === 'add' ? 'menambah' : 'mengurangi'} stok produk ini?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then(result => {
                if (result.isConfirmed) {
                    fetch("{{ route('umkm.warehouse.update-stock') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async res => {
                        let data;
                        try { data = await res.json(); }
                        catch { data = { success: false, message: 'Respons server tidak valid.' }; }
                        Swal.fire({
                            icon: data.success ? 'success' : 'error',
                            title: data.success ? 'Berhasil!' : 'Gagal!',
                            text: data.message || (data.success ? 'Stok berhasil diperbarui.' : 'Terjadi kesalahan.')
                        });
                        if (data.success) {
                            stockModal.style.display = 'none';
                            stockForm.reset();
                            location.reload();
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Terjadi kesalahan server. Coba lagi nanti.', 'error'));
                }
            });
        });
    }

    /* ======================= 🚚 TRANSFER ANTAR GUDANG (Modal tanpa Bootstrap) ======================= */
    const transferModal = document.getElementById('transferModal');
    const openTransferBtn = document.querySelector('.open-transfer'); // tombol yang membuka modal transfer
    const closeTransferBtns = Array.from(document.querySelectorAll('.close-transfer'));
    const productSelect = document.getElementById('productSelect');
    const variationWrapper = document.getElementById('variationWrapper');
    const variationSelect = document.getElementById('variationSelect');
    const transferForm = document.getElementById('transferForm');

    // Buka modal transfer
    if (openTransferBtn && transferModal) {
        openTransferBtn.addEventListener('click', () => {
            // pastikan default state
            transferForm?.reset();
            variationSelect?.innerHTML && (variationSelect.innerHTML = '<option value="">-- Tidak Ada Variasi --</option>');
            variationWrapper && (variationWrapper.style.display = 'none');
            transferModal.style.display = 'flex';

            // set active tab to stock so pagination keeps focus
            activateTab('stock');
        });
    }

    // Tutup modal transfer
    closeTransferBtns.forEach(btn => btn.addEventListener('click', () => {
        transferModal.style.display = 'none';
    }));

    // klik luar modal transfer untuk tutup
    if (transferModal) {
        transferModal.addEventListener('click', e => {
            if (e.target === transferModal) transferModal.style.display = 'none';
        });
    }

    // Muat variasi berdasarkan productSelect
    if (productSelect && variationSelect) {
        productSelect.addEventListener('change', function () {
            const productId = this.value;
            variationSelect.innerHTML = '<option value="">-- Tidak Ada Variasi --</option>';
            variationWrapper && (variationWrapper.style.display = 'none');

            if (!productId) return;

            fetch(`/umkm/product/${productId}/variations`)
                .then(res => res.json())
                .then(data => {
                    // ekspektasi: { success: true, variations: [...] }
                    if (data && data.success && Array.isArray(data.variations) && data.variations.length) {
                        variationSelect.innerHTML = '<option value="">-- Pilih Variasi --</option>';
                        data.variations.forEach(v => {
                            variationSelect.innerHTML += `<option value="${v.id}">${v.name}</option>`;
                        });
                        variationWrapper && (variationWrapper.style.display = 'block');
                    } else {
                        variationWrapper && (variationWrapper.style.display = 'none');
                    }
                })
                .catch(() => {
                    variationWrapper && (variationWrapper.style.display = 'none');
                });
        });
    }

    // Submit transfer
    if (transferForm) {
        transferForm.addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(transferForm);
            const payload = Object.fromEntries(formData.entries());

            if (!payload.from_warehouse_id || !payload.to_warehouse_id || !payload.quantity) {
                Swal.fire('Oops!', 'Pastikan semua kolom sudah diisi.', 'warning');
                return;
            }
            if (payload.from_warehouse_id === payload.to_warehouse_id) {
                Swal.fire('Peringatan', 'Gudang asal dan tujuan tidak boleh sama.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Kirim Barang?',
                text: 'Apakah Anda yakin ingin melakukan transfer stok antar gudang?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kirim',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch("/umkm/warehouse-transfer", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async res => {
                        let data;
                        try { data = await res.json(); }
                        catch { data = { success: false, message: 'Respons server tidak valid.' }; }

                        Swal.fire({
                            icon: data.success ? 'success' : 'error',
                            title: data.success ? 'Berhasil!' : 'Gagal!',
                            text: data.message || 'Proses transfer selesai.'
                        });

                        if (data.success) {
                            transferModal.style.display = 'none';
                            transferForm.reset();
                            setTimeout(() => location.reload(), 1000);
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Terjadi kesalahan saat mengirim data.', 'error'));
                }
            });
        });
    }

    /* ======================= 🔢 PAGINATION MURNI CSS+JS UNTUK STOCK TABLE ======================= */
    // Pagination elements (pastikan ada elemen di blade: #prevPage, #pageInfo, #nextPage)
    const stockTable = document.getElementById('stockTable');
    if (stockTable) {
        const rows = Array.from(stockTable.querySelectorAll('tbody tr'));
        const rowsPerPage = 10;
        let currentPage = 1;

        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');

        function totalPages() {
            return Math.max(1, Math.ceil(rows.length / rowsPerPage));
        }

        function showPage(page) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;

            rows.forEach((row, idx) => {
                row.style.display = (idx >= start && idx < end) ? '' : 'none';
            });

            if (pageInfo) pageInfo.textContent = `Halaman ${page} dari ${totalPages()}`;
            if (prevBtn) prevBtn.disabled = (page === 1);
            if (nextBtn) nextBtn.disabled = (page === totalPages());

            // Hanya simpan halaman terakhir tanpa memaksa tab berpindah
            sessionStorage.setItem('currentStockPage', page);
        }

        // Restore halaman terakhir yang dilihat
        const savedPage = parseInt(sessionStorage.getItem('currentStockPage')) || 1;
        currentPage = Math.min(Math.max(1, savedPage), totalPages());
        showPage(currentPage);

        // Tombol prev / next tanpa refresh
        if (prevBtn) prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
        });

        if (nextBtn) nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage < totalPages()) {
                currentPage++;
                showPage(currentPage);
            }
        });

        // Saat user klik tab stok lagi → tampilkan halaman tersimpan
        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.dataset.tab === 'stock') {
                    const saved = parseInt(sessionStorage.getItem('currentStockPage')) || 1;
                    currentPage = Math.min(Math.max(1, saved), totalPages());
                    showPage(currentPage);
                }
            });
        });
    }

    /* ======================= 🚛 PAGINATION UNTUK TRANSFER TABLE ======================= */
    const transferTable = document.getElementById('transferTable');
    if (transferTable) {
        const rows = Array.from(transferTable.querySelectorAll('tbody tr'));
        const rowsPerPage = 10;
        let currentPage = 1;

        const prevBtn = document.getElementById('prevTransferPage');
        const nextBtn = document.getElementById('nextTransferPage');
        const pageInfo = document.getElementById('transferPageInfo');

        function totalPages() {
            return Math.max(1, Math.ceil(rows.length / rowsPerPage));
        }

        function showTransferPage(page) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;

            rows.forEach((row, idx) => {
                row.style.display = (idx >= start && idx < end) ? '' : 'none';
            });

            if (pageInfo) pageInfo.textContent = `Halaman ${page} dari ${totalPages()}`;
            if (prevBtn) prevBtn.disabled = (page === 1);
            if (nextBtn) nextBtn.disabled = (page === totalPages());

            // Simpan halaman terakhir
            sessionStorage.setItem('currentTransferPage', page);
        }

        // Restore halaman terakhir
        const savedPage = parseInt(sessionStorage.getItem('currentTransferPage')) || 1;
        currentPage = Math.min(Math.max(1, savedPage), totalPages());
        showTransferPage(currentPage);

        // Tombol prev / next
        if (prevBtn) prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                showTransferPage(currentPage);
            }
        });

        if (nextBtn) nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage < totalPages()) {
                currentPage++;
                showTransferPage(currentPage);
            }
        });

        // Saat user kembali ke tab transfer, tampilkan halaman tersimpan
        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.dataset.tab === 'transfer') {
                    const saved = parseInt(sessionStorage.getItem('currentTransferPage')) || 1;
                    currentPage = Math.min(Math.max(1, saved), totalPages());
                    showTransferPage(currentPage);
                }
            });
        });
    }

    // 🔍 Fitur Search Produk (Tanpa Reload)
    document.getElementById('searchProduct').addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('#stockTable tbody tr');

        rows.forEach(row => {
            const productName = row.querySelector('td:first-child').textContent.toLowerCase();
            if (productName.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    const searchInput = document.getElementById('searchTransferProduct');
    const statusSelect = document.getElementById('filterStatus');
    const fromSelect = document.getElementById('filterFromWarehouse');
    const toSelect = document.getElementById('filterToWarehouse');
    const rows = document.querySelectorAll('#transferTable tbody tr');

    function filterTable() {
        const searchValue = searchInput.value.toLowerCase();
        const statusValue = statusSelect.value.toLowerCase();
        const fromValue = fromSelect.value.toLowerCase();
        const toValue = toSelect.value.toLowerCase();

        rows.forEach(row => {
            const product = row.cells[1].textContent.toLowerCase();
            const from = row.cells[2].textContent.toLowerCase();
            const to = row.cells[3].textContent.toLowerCase();
            const status = row.cells[5].textContent.toLowerCase();

            const matchesSearch = product.includes(searchValue);
            const matchesStatus = !statusValue || status.includes(statusValue);
            const matchesFrom = !fromValue || from.includes(fromValue);
            const matchesTo = !toValue || to.includes(toValue);

            row.style.display = (matchesSearch && matchesStatus && matchesFrom && matchesTo) ? '' : 'none';
        });
    }

    [searchInput, statusSelect, fromSelect, toSelect].forEach(el => {
        el.addEventListener('input', filterTable);
    });


}); // end DOMContentLoaded
</script>
@endsection