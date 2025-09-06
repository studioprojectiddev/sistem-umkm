@extends('layouts.app')

@section('title', 'Category Products')

@section('content')
<h1 class="title">Category Product</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="divider">/</li>
    <li><a href="{{ route('umkm.product') }}" class="active">category</a></li>
</ul>
<div class="info-data">
    <div class="card">
        <div class="filters">
            <form method="GET" action="{{ route('umkm.category') }}" class="filters">
                <input type="text" name="name" value="{{ request('name') }}" 
                class="form-control" placeholder="Cari Nama Kategori">
                
                <select name="parent_id" class="form-control">
                    <option value="">-- Semua Parent --</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ request('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
                
                <div class="button-group">
                    <button type="submit" class="filter-btn"><i class='bx bx-filter'></i> Filter</button>
                    <a href="{{ route('umkm.category') }}" class="export-btn"><i class='bx bx-refresh'></i> Reset</a>
                    <button id="openModalBtn" class="add-btn" type="button">
                        <i class='bx bxs-file-plus'></i> Tambah
                    </button>
                </div>
            </form>

        </div>

        {{-- Jika kategori kosong --}}
        @if($categories->isEmpty())
            <p class="text-muted">Belum ada kategori.</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">#</th>
                            <th>Nama</th>
                            <th>Slug</th>
                            <th>Parent</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $index => $category)
                            <tr>
                                <td style="text-align:center;">{{ $index + 1 }}</td>
                                <td>
                                    <div style="display:flex; align-items:center;text-align:center;">
                                        @if($category->icon)
                                            <img src="{{ asset($category->icon) }}" 
                                                alt="{{ $category->name }}" 
                                                style="width:30px;height:30px;object-fit:contain; margin-right:8px;">
                                        @endif
                                        <span>{{ $category->name }}</span>
                                    </div>
                                </td>
                                <td style="text-align:center;">{{ $category->slug }}</td>
                                <td style="text-align:center;">
                                    {{ $category->parent ? $category->parent->name : '-' }}
                                </td>
                                <td style="text-align:center;">
                                    @if($category->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">{{ $category->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="button-container">
                                        <div style="margin-top:16px;">
                                            <a href="javascript:void(0);" 
                                                class="btn btn-sm btn-warning edit-btn"
                                                data-id="{{ $category->id }}"
                                                data-name="{{ $category->name }}"
                                                data-slug="{{ $category->slug }}"
                                                data-code="{{ $category->code }}"
                                                data-parent="{{ $category->parent_id }}"
                                                data-description="{{ $category->description }}"
                                                data-sort="{{ $category->sort_order }}"
                                                data-status="{{ $category->is_active }}"
                                                data-icon="{{ $category->icon }}"
                                                data-banner="{{ $category->banner }}"
                                                data-meta_title="{{ $category->meta_title }}"
                                                data-meta_keywords="{{ $category->meta_keywords }}"
                                                data-meta_description="{{ $category->meta_description }}">Edit
                                            </a>
                                        </div>
                                        <form class="delete-form" action="{{ route('umkm.categories.destroy', $category->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="delete-btn swal-confirm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="custom-pagination">
                    <button id="prevPage" class="btn-page" {{ $categories->onFirstPage() ? 'disabled' : '' }}>
                        &laquo; Previous
                    </button>

                    <span class="page-info">
                        Page {{ $categories->currentPage() }} of {{ $categories->lastPage() }}
                    </span>

                    <button id="nextPage" class="btn-page" {{ $categories->currentPage() === $categories->lastPage() ? 'disabled' : '' }}>
                        Next &raquo;
                    </button>
                </div>

            </div>
        @endif

    </div>
</div>

<!-- Modal Tambah Category -->
<div id="createModal" class="modal-overlay hidden">
    <div class="modal-content2">
        <span id="closeModalBtn" class="close-btn">&times;</span>
        <h2 class="modal-title">Tambah Category</h2>

        <form action="{{ route('umkm.categories.store') }}" method="POST" class="modal-form" enctype="multipart/form-data">
            @csrf

            <div class="form-row">
                <!-- Nama kategori -->
                <div class="form-group">
                    <label for="name">Nama Kategori</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
    
                <!-- Slug -->
                <div class="form-group">
                    <label for="slug">Slug (Otomatis)</label>
                    <input type="text" name="slug" id="slug" class="form-control">
                </div>
            </div>

            <div class="form-row">
                <!-- Code -->
                <div class="form-group">
                    <label for="code">Kode Kategori (opsional)</label>
                    <input type="text" name="code" id="code" class="form-control">
                </div>
    
                <!-- Parent -->
                <div class="form-group">
                    <label for="parent_id">Parent Kategori</label>
                    <select name="parent_id" id="parent_id" class="form-control">
                        <option value="">-- Tanpa Parent --</option>
                        @foreach($categories as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                 <!-- Deskripsi -->
                 <div class="form-group">
                     <label for="description">Deskripsi</label>
                     <textarea name="description" id="description" style="padding:10px;" class="form-control full-width" rows="3"></textarea>
                 </div>
             </div>

             <div class="form-row">
                 <!-- Icon -->
                 <div class="form-group">
                     <label for="icon">Icon (opsional)</label>
                     <input type="file" name="icon" id="icon" class="form-control">
                 </div>
     
                 <!-- Banner -->
                 <div class="form-group">
                     <label for="banner">Banner (opsional)</label>
                     <input type="file" name="banner" id="banner" class="form-control">
                 </div>
             </div>

            <div class="form-row">
                <!-- Sort Order -->
                <div class="form-group mb-3">
                    <label for="sort_order">Urutan Tampil</label>
                    <input type="number" name="sort_order" id="sort_order" class="form-control" value="0">
                </div>
    
                <!-- Status -->
                <div class="form-group mb-3">
                    <label for="is_active">Status</label>
                    <select name="is_active" id="is_active" class="form-control">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>

            <!-- SEO -->
            <hr><br>
            <h2 class="modal-title">SEO</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="meta_title">Meta Title</label>
                    <input type="text" name="meta_title" id="meta_title" class="form-control">
                </div>
                <div class="form-group">
                    <label for="meta_keywords">Meta Keywords</label>
                    <input type="text" name="meta_keywords" id="meta_keywords" class="form-control" placeholder="pisahkan dengan koma">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="meta_description">Meta Description</label>
                    <textarea name="meta_description" style="padding:10px;" id="meta_description" class="form-control full-width" rows="2"></textarea>
                </div>
            </div>

            <button type="submit" class="submit-btn">💾 Simpan</button>
        </form>

    </div>
</div>

<!-- Modal Edit Category -->
<div id="editModal" class="modal-overlay hidden">
    <div class="modal-content2">
        <span id="closeEditModalBtn" class="close-btn">&times;</span>
        <h2 class="modal-title">Edit Category</h2>

        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="id" id="edit_id">

            <div class="form-row">
                <div class="form-group">
                    <label for="edit_name">Nama Kategori</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_slug">Slug</label>
                    <input type="text" name="slug" id="edit_slug" class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="edit_code">Kode Kategori</label>
                    <input type="text" name="code" id="edit_code" class="form-control">
                </div>
                <div class="form-group">
                    <label for="edit_parent_id">Parent Kategori</label>
                    <select name="parent_id" id="edit_parent_id" class="form-control">
                        <option value="">-- Tanpa Parent --</option>
                        @foreach($categories as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group full-width">
                    <label for="edit_description">Deskripsi</label>
                    <textarea name="description" style="padding:10px;" id="edit_description" class="form-control full-width" rows="3"></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="edit_icon">Icon :</label>
                    <!-- Preview icon lama -->
                    <div class="mb-2">
                        <img id="current_icon" src="" alt="Icon Preview" 
                            style="width:60px;height:60px;object-fit:cover;
                                    border:1px solid #ddd;border-radius:6px;
                                    display:none;">
                    </div>
                    <!-- Input file -->
                    <input type="file" name="icon" id="edit_icon" class="form-control">
                </div>

                <div class="form-group">
                    <label for="edit_banner">Banner :</label>
                    <!-- Preview banner lama -->
                    <div class="mb-2">
                        <img id="current_banner" src="" alt="Banner Preview" 
                            style="width:60px;height:60px;object-fit:cover;
                                    border:1px solid #ddd;border-radius:6px;
                                    display:none;">
                    </div>
                    <!-- Input file -->
                    <input type="file" name="banner" id="edit_banner" class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="edit_sort_order">Urutan Tampil</label>
                    <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                </div>
                <div class="form-group">
                    <label for="edit_is_active">Status</label>
                    <select name="is_active" id="edit_is_active" class="form-control">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>

            <hr><br>
            <h2 class="modal-title">SEO</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="edit_meta_title">Meta Title</label>
                    <input type="text" name="meta_title" id="edit_meta_title" class="form-control">
                </div>
                <div class="form-group">
                    <label for="edit_meta_keywords">Meta Keywords</label>
                    <input type="text" name="meta_keywords" id="edit_meta_keywords" class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group full-width">
                    <label for="edit_meta_description">Meta Description</label>
                    <textarea name="meta_description" style="padding:10px" id="edit_meta_description" class="form-control full-width" rows="2"></textarea>
                </div>
            </div>

            <button type="submit" class="submit-btn">💾 Update</button>
        </form>
    </div>
</div>

@endsection