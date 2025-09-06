@extends('layouts.app')

@section('title', 'List Product')

@section('content')
<h1 class="title">List Product</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="divider">/</li>
    <li><a href="{{ route('umkm.product') }}" class="active">list product</a></li>
</ul>
<div class="info-data">
    <div class="card">
        <div class="tabs-menu">
            <button class="tab-link active" data-target="productContent">List Produk</button>
            <button class="tab-link" data-target="variationContent">Variasi Produk</button>
        </div>

        <div id="productContent" class="tab-pane active">
            {{-- Tombol tambah produk --}}
            <div class="filters">
                <form method="GET" action="{{ route('umkm.product') }}" class="filters">
                    <!-- Pencarian Nama Produk -->
                    <input type="text" name="name" value="{{ request('name') }}" 
                        class="form-control" placeholder="Cari Nama Produk">

                    <!-- Filter Kategori -->
                    <select name="category_id" class="form-control">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Filter Status Aktif -->
                    <select name="is_active" class="form-control">
                        <option value="">-- Semua Status --</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>

                    <div class="button-group">
                        <button type="submit" class="filter-btn"><i class='bx bx-filter'></i> Filter</button>
                        <a href="{{ route('umkm.product') }}" class="export-btn"><i class='bx bx-refresh'></i> Reset</a>
                        <button id="openModalProductBtn" class="add-btn" type="button">
                            <i class='bx bxs-file-plus'></i> Tambah
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabel daftar produk --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Thumbnail</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($product->thumbnail)
                                        <img src="{{ asset($product->thumbnail) }}" alt="{{ $product->name }}" width="50">
                                    @else
                                        <span class="text-muted">No Image</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">{{ $product->name }}</td>
                                <td style="text-align:center;">{{ $product->category->name ?? '-' }}</td>
                                <td style="text-align:center;">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                <td style="text-align:center;">{{ $product->stock }} {{ $product->unit }}</td>
                                <td style="text-align:center;">
                                    @if($product->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="button-container">
                                        <div style="margin-top:16px;">
                                            <a href="javascript:void(0);" 
                                                class="btn btn-sm btn-warning edit-btn"
                                                data-id="{{ $product->id }}"
                                                data-name="{{ $product->name }}"
                                                data-sku="{{ $product->sku }}"
                                                data-barcode="{{ $product->barcode }}"
                                                data-category_id="{{ $product->category_id }}"
                                                data-price="{{ $product->price }}"
                                                data-discount_price="{{ $product->discount_price }}"
                                                data-cost_price="{{ $product->cost_price }}"
                                                data-stock="{{ $product->stock }}"
                                                data-min_stock="{{ $product->min_stock }}"
                                                data-unit="{{ $product->unit }}"
                                                data-product_type="{{ $product->product_type }}"
                                                data-expiry_date="{{ $product->expiry_date }}"
                                                data-batch_number="{{ $product->batch_number }}"
                                                data-description="{{ $product->description }}"
                                                data-is_active="{{ $product->is_active }}"
                                                data-is_featured="{{ $product->is_featured }}"
                                                data-is_promo="{{ $product->is_promo }}"
                                                data-promo_price="{{ $product->promo_price }}"
                                                data-promo_start="{{ $product->promo_start }}"
                                                data-promo_end="{{ $product->promo_end }}"
                                                data-meta_title="{{ $product->meta_title }}"
                                                data-meta_keywords="{{ $product->meta_keywords }}"
                                                data-meta_description="{{ $product->meta_description }}"
                                                data-thumbnail="{{ $product->thumbnail }}"
                                                data-images='{{ $product->images }}'>Edit
                                            </a>
                                        </div>
                                        <form class="delete-form" action="{{ route('umkm.product.destroy', $product->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="delete-product-btn swal-confirm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada produk</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="custom-pagination">
                    <button id="prevPage" class="btn-page" {{ $products->onFirstPage() ? 'disabled' : '' }}>
                        &laquo; Previous
                    </button>

                    <span class="page-info">
                        Page {{ $products->currentPage() }} of {{ $products->lastPage() }}
                    </span>

                    <button id="nextPage" class="btn-page" {{ $products->currentPage() === $products->lastPage() ? 'disabled' : '' }}>
                        Next &raquo;
                    </button>
                </div>
            </div>
        </div>

        {{-- TAB VARIASI --}}
        <div id="variationContent" class="tab-pane">
            <div class="variation-header">
                
            </div>

            {{-- FILTER VARIASI --}}
            <div class="filters">
                <form method="GET" action="" class="filter-form">
                    <input type="text" name="product_name" placeholder="Cari produk..." value="{{ request('product_name') }}">

                    <select name="attribute">
                        <option value="">Semua Atribut</option>
                        @foreach ($variationAttributes as $attribute)
                            <option value="{{ $attribute->id }}" {{ request('attribute') == $attribute->id ? 'selected' : '' }}>
                                {{ $attribute->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-filter">Filter</button>
                    <button id="openVariationModal" class="add-btn" type="button">
                        <i class='bx bxs-file-plus'></i> Tambah
                    </button>
                </form>
            </div>

            {{-- TABEL VARIASI --}}
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Variasi</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($variations as $variation)
                            <tr>
                                <td>{{ $variation->product->name }}</td>
                                <td>
                                    @php
                                        // kelompokkan berdasarkan attribute name
                                        $grouped = $variation->options->groupBy(function($opt) {
                                            return $opt->attribute->name;
                                        });
                                    @endphp

                                    @foreach ($grouped as $attrName => $opts)
                                        <div>
                                            <span class="badge">{{ $attrName }}</span> - 
                                            {{ $opts->pluck('value')->implode(', ') }}
                                        </div>
                                    @endforeach
                                </td>
                                <td>Rp {{ number_format($variation->price, 0, ',', '.') }}</td>
                                <td>{{ $variation->stock }}</td>
                                <td>
                                    <div class="button-container">
                                        <div style="margin-top:16px;">
                                            <a href="javascript:void(0);" 
                                                class="btn btn-sm btn-warning variation-edit-btn"
                                                data-id="{{ $variation->id }}"
                                                data-product="{{ $variation->product_id }}"
                                                data-price="{{ $variation->price }}"
                                                data-stock="{{ $variation->stock }}"
                                                data-sku="{{ $variation->sku }}"        
                                                data-weight="{{ $variation->weight }}"  
                                                data-options='@json(
                                                    $variation->options->map(fn($opt) => [
                                                        "attribute_id" => $opt->attribute_id,
                                                        "option_id"    => $opt->id
                                                    ])
                                                )'
                                            >
                                                Edit
                                            </a>
                                        </div>

                                        <form method="POST" action="{{ route('umkm.variation.destroy', $variation->id) }}" class="delete-form" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn-variasi-delete swal-confirm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-data">Belum ada variasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="pagination-container">
                {{ $variations->links() }}
            </div>
        </div>


    </div>
</div>

<div id="createModalProduct" class="modal-overlay hidden">
    <div class="modal-content3">
        <span id="closeModalProductBtn" class="close-btn">&times;</span>

        <h2 class="modal-title">Tambah Produk</h2>

        <form action="{{ route('umkm.product.store') }}" method="POST" class="modal-form" enctype="multipart/form-data">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nama Produk</label>
                    <input type="text" name="name" id="name" required>
                </div>

                <div class="form-group">
                    <label for="sku">SKU (opsional)</label>
                    <input type="text" name="sku" id="sku">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="barcode">Barcode (opsional)</label>
                    <input type="text" name="barcode" id="barcode">
                </div>

                <div class="form-group">
                    <label for="category_id">Kategori</label>
                    <select name="category_id" id="category_id">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Harga Jual</label>
                    <input type="number" step="0.01" name="price" id="price" required>
                </div>

                <div class="form-group">
                    <label for="discount_price">Harga Diskon</label>
                    <input type="number" step="0.01" name="discount_price" id="discount_price">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cost_price">Harga Modal</label>
                    <input type="number" step="0.01" name="cost_price" id="cost_price">
                </div>
                <div class="form-group"></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="stock">Stok</label>
                    <input type="number" name="stock" id="stock" value="0">
                </div>

                <div class="form-group">
                    <label for="min_stock">Minimal Stok</label>
                    <input type="number" name="min_stock" id="min_stock" value="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="unit">Satuan</label>
                    <input type="text" name="unit" id="unit" value="pcs">
                </div>

                <div class="form-group">
                    <label for="product_type">Jenis Produk</label>
                    <select name="product_type" id="product_type">
                        <option value="goods">Barang</option>
                        <option value="service">Jasa</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="expiry_date">Tanggal Expired</label>
                    <input type="date" name="expiry_date" id="expiry_date">
                </div>

                <div class="form-group">
                    <label for="batch_number">Batch Number</label>
                    <input type="text" name="batch_number" id="batch_number">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="thumbnail">Thumbnail</label>
                    <input type="file" name="thumbnail" id="thumbnail" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="images">Gambar Tambahan</label>
                    <input type="file" name="images[]" id="images" multiple accept="image/*">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea name="description" class="form-control full-width" style="padding:10px;" id="description"></textarea>
                </div>

                <div class="form-group">
                    <label for="is_active">Status</label>
                    <select name="is_active" id="is_active">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="is_featured">Produk Unggulan?</label>
                    <select name="is_featured" id="is_featured">
                        <option value="0">Tidak</option>
                        <option value="1">Ya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="is_promo">Promo?</label>
                    <select name="is_promo" id="is_promo">
                        <option value="0">Tidak</option>
                        <option value="1">Ya</option>
                    </select>
                </div>
            </div>

            <div id="promo_fields" class="hidden">
                <div class="form-row">
                    <div class="form-group">
                        <label for="promo_price">Harga Promo</label>
                        <input type="number" step="0.01" name="promo_price" id="promo_price">
                    </div>

                    <div class="form-group">
                        <label for="promo_start">Promo Mulai</label>
                        <input type="date" name="promo_start" id="promo_start">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="promo_end">Promo Selesai</label>
                        <input type="date" name="promo_end" id="promo_end">
                    </div>
                    <div class="form-group"></div>
                </div>
            </div><hr><br>

            <div class="form-row">
                <div class="form-group">
                    <label for="meta_title">Meta Title</label>
                    <input type="text" name="meta_title" id="meta_title">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="meta_keywords">Meta Keywords</label>
                    <input type="text" name="meta_keywords" id="meta_keywords">
                </div>

                <div class="form-group">
                    <label for="meta_description">Meta Description</label>
                    <textarea name="meta_description" class="form-control full-width" style="padding:10px;" id="meta_description"></textarea>
                </div>
            </div>

            <button type="submit" class="submit-btn">💾 Simpan</button>
        </form>
    </div>
</div>

<!-- Modal Edit Produk -->
<div id="editModalProduct" class="modal-overlay hidden">
    <div class="modal-content3">
        <span class="close-btn absolute top-2 right-3 text-2xl cursor-pointer" data-close="#editModalProduct">&times;</span>
        <h2 class="modal-title">Edit Produk</h2>

        <form id="editFormProduct" method="POST" class="modal-form" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <input type="hidden" id="edit_id" name="id">

            <!-- Nama & SKU -->
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_name">Nama Produk</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_sku">SKU (opsional)</label>
                    <input type="text" name="sku" id="edit_sku">
                </div>
            </div>

            <!-- Barcode & Kategori -->
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_barcode">Barcode</label>
                    <input type="text" name="barcode" id="edit_barcode">
                    <img id="current_barcode" src="" alt="Barcode" style="width:100px; margin-top:5px; display:none;">
                </div>
                <div class="form-group">
                    <label for="edit_category_id">Kategori</label>
                    <select name="category_id" id="edit_category_id">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Harga & Diskon -->
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_price">Harga Jual</label>
                    <input type="number" step="0.01" name="price" id="edit_price" required>
                </div>
                <div class="form-group">
                    <label for="edit_discount_price">Harga Diskon</label>
                    <input type="number" step="0.01" name="discount_price" id="edit_discount_price">
                </div>
            </div>

            <!-- Harga Modal & Stok -->
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_cost_price">Harga Modal</label>
                    <input type="number" step="0.01" name="cost_price" id="edit_cost_price">
                </div>
                <div class="form-group">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="edit_stock">Stok</label>
                    <input type="number" name="stock" id="edit_stock">
                </div>
                <div class="form-group">
                    <label for="edit_min_stock">Minimal Stok</label>
                    <input type="number" name="min_stock" id="edit_min_stock">
                </div>
            </div>

            <!-- Min Stok, Unit, Jenis -->
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_unit">Satuan</label>
                    <input type="text" name="unit" id="edit_unit">
                </div>
                <div class="form-group">
                    <label for="edit_product_type">Jenis Produk</label>
                    <select name="product_type" id="edit_product_type">
                        <option value="goods">Barang</option>
                        <option value="service">Jasa</option>
                    </select>
                </div>
            </div>

            <!-- Expired & Batch -->
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_expiry_date">Tanggal Expired</label>
                    <input type="date" name="expiry_date" id="edit_expiry_date">
                </div>
                <div class="form-group">
                    <label for="edit_batch_number">Batch Number</label>
                    <input type="text" name="batch_number" id="edit_batch_number">
                </div>
            </div>

            <!-- Thumbnail & Images -->
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_thumbnail">Thumbnail</label>
                    <input type="file" name="thumbnail" id="edit_thumbnail" accept="image/*">
                    <img id="current_thumbnail" src="" alt="Thumbnail" style="width:80px; margin-top:5px; display:none;">
                </div>
                <div class="form-group">
                    <label for="edit_images">Gambar Tambahan</label>
                    <input type="file" name="images" id="edit_images" accept="image/*">
                    <img id="current_images" src="" alt="Images" style="width:80px; margin-top:5px; display:none;">
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_description">Deskripsi</label>
                    <textarea name="description" id="edit_description" class="form-control full-width" style="padding:10px;"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_is_active">Status</label>
                    <select name="is_active" id="edit_is_active">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>

            <!-- Status, Featured, Promo -->
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_is_featured">Produk Unggulan?</label>
                    <select name="is_featured" id="edit_is_featured">
                        <option value="0">Tidak</option>
                        <option value="1">Ya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_is_promo">Promo?</label>
                    <select name="is_promo" id="edit_is_promo" class="promo-toggle">
                        <option value="0">Tidak</option>
                        <option value="1">Ya</option>
                    </select>
                </div>
            </div>

            <!-- Promo Fields -->
            <div id="edit_promo_fields" class="hidden">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_promo_price">Harga Promo</label>
                        <input type="number" step="0.01" name="promo_price" id="edit_promo_price">
                    </div>
                    <div class="form-group">
                        <label for="edit_promo_start">Promo Mulai</label>
                        <input type="date" name="promo_start" id="edit_promo_start">
                    </div>
                    <div class="form-group">
                        <label for="edit_promo_end">Promo Selesai</label>
                        <input type="date" name="promo_end" id="edit_promo_end">
                    </div>
                </div>
            </div><hr><br>

            <!-- SEO -->
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_meta_title">Meta Title</label>
                    <input type="text" name="meta_title" id="edit_meta_title">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="edit_meta_keywords">Meta Keywords</label>
                    <input type="text" name="meta_keywords" id="edit_meta_keywords">
                </div>
                <div class="form-group">
                    <label for="edit_meta_description">Meta Description</label>
                    <textarea name="meta_description" id="edit_meta_description" class="form-control full-width" style="padding:10px;"></textarea>
                </div>
            </div>

            <button type="submit" class="submit-btn">💾 Update</button>
        </form>
    </div>
</div>

<div id="createVariationModal" class="modal-overlay hidden">
    <div class="modal-content2">
        <span id="closeModalVariationBtn" class="close-btn">&times;</span>
        <h2 class="modal-title">Kelola Variasi Produk</h2>

        {{-- ================= FORM 1: Tambah Atribut & Opsi ================= --}}
        <form id="attributeForm" method="POST" action="{{ route('umkm.atribut.store') }}">
            @csrf
            <h3 class="section-title">Tambah Atribut Baru</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="attribute_name">Nama Atribut</label>
                    <input type="text" name="name" id="attribute_name" placeholder="Contoh: Warna, Ukuran, Rasa" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="options">Opsi</label>
                    <div id="optionWrapper">
                        <div class="variation-row">
                            <input type="text" name="options[]" placeholder="Masukkan opsi (contoh: Merah)" required>
                            <button type="button" class="btn-remove-row">X</button>
                        </div>
                    </div>
                    <button type="button" id="addOptionRow" class="btn-add-row">+ Tambah Opsi</button>
                </div>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn-submit">Simpan Atribut</button>
            </div>
        </form><br>

        <hr>

        {{-- ================= FORM 2: Tambah Variasi Produk ================= --}}
        <form id="variationForm" method="POST" action="{{ route('umkm.variasi.store') }}" enctype="multipart/form-data">
            @csrf
            <h3 class="section-title">Tambah Variasi Produk</h3>

            <div class="form-row">
                {{-- Pilih Produk --}}
                <div class="form-group">
                    <label for="product_id">Produk</label>
                    <select name="product_id" id="product_id" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                {{-- Pilih Atribut + Opsi --}}
                <div class="form-group">
                    <label for="attributes">Variasi</label>
                    <div id="variationAttributes">
                        <div class="variation-row">
                            <select name="attributes[]" class="attribute-select" required>
                                <option value="">-- Pilih Atribut --</option>
                                @foreach ($variationAttributes as $attribute)
                                    <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                                @endforeach
                            </select>

                            <select name="options[]" class="option-select" required>
                                <option value="">-- Pilih Opsi --</option>
                                {{-- opsi diisi via JS --}}
                            </select>

                            <button type="button" class="btn-remove-row">X</button>
                        </div>
                    </div>
                    <button type="button" id="addVariationRow" class="btn-add-row">+ Tambah Variasi</button>
                </div>
            </div>

            <div class="form-row">
                {{-- Harga --}}
                <div class="form-group">
                    <label for="price">Harga</label>
                    <input type="number" name="price" id="price" placeholder="Masukkan harga" required>
                </div>

                {{-- Stok --}}
                <div class="form-group">
                    <label for="stock">Stok</label>
                    <input type="number" name="stock" id="stock" placeholder="Jumlah stok" required>
                </div>
            </div>

            <div class="form-row">
                {{-- Berat --}}
                <div class="form-group">
                    <label for="weight">Berat (gram)</label>
                    <input type="number" name="weight" id="weight" placeholder="Berat dalam gram" required>
                </div>

                {{-- Gambar --}}
                <div class="form-group">
                    <label for="image">Foto Variasi</label>
                    <input type="file" name="image" id="image" accept="image/*">
                </div>
            </div>

            <div class="form-row">
                {{-- SKU --}}
                <div class="form-group">
                    <label for="sku">SKU (opsional)</label>
                    <input type="text" name="sku" id="sku" placeholder="Kode SKU unik">
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <label for="edit_is_active">Status</label>
                    <select name="is_active" id="edit_is_active">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn-submit">Simpan Variasi</button>
            </div>
        </form>
    </div>
</div>
<div id="variationEditModal" class="modal-overlay hidden">
    <div class="modal-content2">
        <span class="close-btn absolute top-2 right-3 text-2xl cursor-pointer" data-close="#variationEditModal">&times;</span>
        <h3 class="modal-title">Edit Variasi Produk</h3>

        <form id="variationEditForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="variation_id">
            <input type="hidden" id="variation_product" name="product_id" class="form-control" readonly>

            {{-- Variasi --}}
            <div class="form-row">
                <div class="form-group">
                    <label for="attributes">Variasi</label>
                    <div id="editVariationAttributes">
                        @if(isset($variation) && $variation->options->count())
                        @foreach ($variation->options as $option)
                            <div class="variation-row">
                                {{-- Pilih Atribut --}}
                                <select name="attributes[]" class="attribute-select" required>
                                    <option value="">-- Pilih Atribut --</option>
                                    @foreach ($variationAttributes as $attribute)
                                        <option value="{{ $attribute->id }}" 
                                            {{ $attribute->id == $option->attribute_id ? 'selected' : '' }}>
                                            {{ $attribute->name }}
                                        </option>
                                    @endforeach
                                </select>

                                {{-- Pilih Opsi --}}
                                <select name="options[]" class="option-select" required>
                                    <option value="">-- Pilih Opsi --</option>
                                    @foreach ($option->attribute->options as $opt)
                                        <option value="{{ $opt->id }}" 
                                            {{ $opt->id == $option->id ? 'selected' : '' }}>
                                            {{ $opt->value }}
                                        </option>
                                    @endforeach
                                </select>

                                <button type="button" class="btn-remove-row">X</button>
                            </div>
                        @endforeach

                        @else
                            {{-- Default kosong (baru tambah variasi) --}}
                            <div class="variation-row">
                                <select name="attributes[]" class="attribute-select" required>
                                    <option value="">-- Pilih Atribut --</option>
                                    @foreach ($variationAttributes as $attribute)
                                        <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                                    @endforeach
                                </select>

                                <select name="options[]" class="option-select" required>
                                    <option value="">-- Pilih Opsi --</option>
                                </select>

                                <button type="button" class="btn-remove-row">X</button>
                            </div>
                        @endif
                    </div>

                    <button type="button" id="addEditVariationRow" class="btn-add-row">+ Tambah Variasi</button>
                </div>
            </div>

            {{-- Harga & Stok --}}
            <div class="form-row">
                <div class="form-group">
                    <label for="variation_price">Harga</label>
                    <input type="number" name="price" id="variation_price" class="form-control" value="{{ $variation->price ?? '' }}" required>
                </div>

                <div class="form-group">
                    <label for="variation_stock">Stok</label>
                    <input type="number" name="stock" id="variation_stock" class="form-control" value="{{ $variation->stock ?? '' }}" required>
                </div>
            </div>

            {{-- Berat & Gambar --}}
            <div class="form-row">
                <div class="form-group">
                    <label for="variation_weight">Berat (gram)</label>
                    <input type="number" name="weight" id="variation_weight" class="form-control" value="{{ $variation->weight ?? '' }}" required>
                </div>

                <div class="form-group">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <label for="variation_image">Foto Variasi</label>
                        <!-- Tombol upload -->
                        <input 
                            type="file" 
                            name="image" 
                            id="variation_image" 
                            accept="image/*" 
                            style="width: auto; height: auto; padding: 6px 12px; font-size: 14px;"
                        >

                        <!-- Preview -->
                        @if(isset($variation->image))
                            <img id="variation_image_preview" src="{{ asset($variation->image) }}" alt="Preview" style="max-height: 80px; border:1px solid #ddd; padding:4px; border-radius:6px;">
                        @else
                            <img id="variation_image_preview" src="" alt="Preview" style="max-height: 80px; border:1px solid #ddd; padding:4px; border-radius:6px; display:none;">
                        @endif
                    </div>
                </div>
            </div>

            {{-- SKU & Status --}}
            <div class="form-row">
                <div class="form-group">
                    <label for="variation_sku">SKU (opsional)</label>
                    <input type="text" name="sku" id="variation_sku" class="form-control" value="{{ $variation->sku ?? '' }}">
                </div>

                <div class="form-group">
                    <label for="edit_is_active">Status</label>
                    <select name="is_active" id="edit_is_active">
                        <option value="1" {{ isset($variation) && $variation->is_active ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ isset($variation) && !$variation->is_active ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    window.variationAttributes = @json($variationAttributes);
    window.variationOptions = @json(
        $variationOptions->groupBy('attribute_id')->map(function($items) {
            return $items->map(function($item) {
                return [
                    'id' => $item->id,
                    'value' => $item->value
                ];
            });
        })
    );
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Event saat atribut dipilih
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('attribute-select')) {
            let attributeId = e.target.value;
            let optionSelect = e.target.closest('.variation-row').querySelector('.option-select');

            if (attributeId) {
                fetch(`/umkm/variation-options/${attributeId}`)
                    .then(res => res.json())
                    .then(data => {
                        optionSelect.innerHTML = '<option value="">-- Pilih Opsi --</option>';
                        data.forEach(opt => {
                            optionSelect.innerHTML += `<option value="${opt.id}">${opt.value}</option>`;
                        });
                    });
            } else {
                optionSelect.innerHTML = '<option value="">-- Pilih Opsi --</option>';
            }
        }
    });
});
</script>

@endsection