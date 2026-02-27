@extends('layouts.app')

@section('title', 'Detail Produk')

@section('content')
<style>
    .detail-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 16px;
        font-family: 'Segoe UI', Tahoma, sans-serif;
        color: #333;
    }
    .page-title {
        font-size: 28px;
        font-weight: 600;
        margin-bottom: 24px;
    }

    .search-form {
        display: flex;
        gap: 10px;
        margin-bottom: 24px;
    }
    .search-input {
        flex: 1;
        padding: 10px 14px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
    }
    .search-input:focus {
        outline: none;
        border-color: #4a90e2;
        box-shadow: 0 0 4px #4a90e266;
    }
    .search-btn {
        padding: 10px 20px;
        background: #4a90e2;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.3s;
    }
    .search-btn:hover {
        background: #357ac8;
    }

    .table-wrapper {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #eee;
    }
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }
    .custom-table thead {
        background: #f7f7f7;
    }
    .custom-table th,
    .custom-table td {
        padding: 14px 18px;
        text-align: left;
        border-bottom: 1px solid #eee;
        font-size: 15px;
    }
    .custom-table th {
        font-weight: 600;
        color: #444;
    }
    .text-center { text-align: center; }

    /* Produk */
    .product-col { min-width: 260px; }
    .product-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #ddd;
    }
    .thumb-empty {
        width: 50px;
        height: 50px;
        background: #eee;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #aaa;
        font-size: 12px;
        border-radius: 6px;
        border: 1px dashed #ccc;
    }
    .product-text {
        display: flex;
        flex-direction: column;
    }
    .product-name {
        font-weight: 600;
        margin-bottom: 2px;
    }
    .sku {
        font-size: 13px;
        color: #666;
    }
    .badge {
        display: inline-block;
        margin-top: 4px;
        padding: 2px 6px;
        font-size: 12px;
        color: #fff;
        background: #999;
        border-radius: 4px;
    }

    /* Tombol Detail */
    .btn-detail {
        display: inline-block;
        padding: 8px 14px;
        background: #4a90e2;
        color: #fff;
        text-decoration: none;
        border-radius: 6px;
        font-size: 14px;
        transition: background 0.3s;
    }
    .btn-detail:hover {
        background: #357ac8;
    }

    /* Info penjualan */
    .sold-info {
        font-size: 13px;
        color: #777;
    }

    /* Empty Row */
    .empty-row {
        text-align: center;
        padding: 30px 0;
        color: #999;
    }

    /* Pagination wrapper */
    .pagination-wrapper {
        margin-top: 24px;
        text-align: center;
    }

    .modal-overlay {
        display: none !important;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        align-items: center;
        justify-content: center;
        z-index: 999;
    }

    .modal-overlay.active { display: flex !important; }

    .modal-box {
        background: #fff;
        padding: 20px 24px;
        border-radius: 10px;
        width: 90%;
        max-width: 480px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        position: relative;
    }

    .modal-title {
        margin-top: 0;
        margin-bottom: 16px;
        font-size: 20px;
        font-weight: 600;
    }

    .modal-close {
        position: absolute;
        top: 12px;
        right: 16px;
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #555;
    }

    .btn-detail {
        background: #0077cc;
        color: #fff;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 14px;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-detail:hover {
        background: #005fa3;
    }


</style>
<h1 class="title">Detail Produk – Ringkasan</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="divider">/</li>
    <li><a href="{{ route('umkm.product.product_detail') }}" class="active">Detail Product</a></li>
</ul>
<div class="info-data">
    <div class="card">
        <form method="GET" action="" class="search-form">
            <input type="text" name="q" class="search-input"
                placeholder="Cari nama produk atau SKU..." value="{{ request('q') }}">
            <button type="submit" class="search-btn">Cari</button>
        </form>

        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Varian</th>
                        <th>Harga Jual</th>
                        <th>Stok Total</th>
                        <th>Total Penjualan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($products as $p)
                    <tr>
                        <td class="product-col">
                            <div class="product-info">
                                @if($p->thumbnail)
                                    <img src="{{ asset($p->thumbnail) }}" alt="{{ $p->name }}"
                                        class="thumb">
                                @else
                                    <div class="thumb thumb-empty">No Img</div>
                                @endif
                                <div class="product-text">
                                    <span class="product-name">{{ $p->name }}</span>
                                    <span class="sku">SKU: {{ $p->sku ?? '-' }}</span>
                                    @if(!$p->is_active)
                                        <span class="badge">Nonaktif</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="text-center">{{ $p->variant_count }}</td>
                        <td>Rp. {{ number_format($p->price) }}</td>
                        <td>{{ $p->stock_total }}</td>
                        <td>
                            <strong>Rp. {{ number_format($p->total_sales) }}</strong>
                            <div class="sold-info">{{ number_format($p->total_qty) }} terjual</div>
                        </td>

                        <td class="text-center">
                            <a href="javascript:void(0)"
                            class="btn-detail"
                            data-id="{{ $p->id }}"
                            data-name="{{ $p->name }}"
                            data-sku="{{ $p->sku ?? '-' }}"
                            data-price="{{ $p->price }}"
                            data-stock="{{ $p->stock_total }}"        {{-- stok total --}}
                            data-stockproduct="{{ $p->stock_product }}" {{-- stok induk (tanpa varian) --}}
                            data-variants="{{ $p->variant_count }}"
                            data-sales="{{ $p->total_sales }}"
                            data-sold="{{ $p->total_qty }}"
                            >Lihat Detail</a>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-row">
                            Tidak ada produk ditemukan.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $products->withQueryString()->links() }}
        </div>
    </div>
</div>

<div id="productModal" class="modal-overlay">
  <div class="modal-box">
    <button class="modal-close">&times;</button>
    <h2 class="modal-title">Detail Produk</h2>

    <div class="modal-content">
        <p><strong>Nama:</strong> <span id="modalName"></span></p>
        <p><strong>SKU:</strong> <span id="modalSku"></span></p>
        <p><strong>Harga Jual:</strong> <span id="modalPrice"></span></p>
        <p><strong>Stok Produk (tanpa varian):</strong>
            <span id="modalStockProduct"></span>
        </p>
        <p><strong>Stok Total:</strong> <span id="modalStock"></span></p>
        <p><strong>Jumlah Varian:</strong> <span id="modalVariants"></span></p>
        <p><strong>Total Penjualan:</strong> <span id="modalSales"></span>
           (<span id="modalSold"></span> terjual)
        </p>

        {{-- ✅ Tabel Varian --}}
        <div id="variantWrapper" style="margin-top:20px;">
            <h4 style="margin-bottom:8px;">Daftar Varian</h4>
            <table id="variantTable" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f0f0f0;">
                        <th style="padding:6px;text-align:left;">Nama</th>
                        <th style="padding:6px;text-align:right;">Harga</th>
                        <th style="padding:6px;text-align:right;">Stok</th>
                        <th style="padding:6px;text-align:right;">Terjual</th>
                    </tr>
                </thead>
                <tbody id="variantBody">
                    <tr><td colspan="3" style="padding:8px;text-align:center;color:#888;">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal       = document.getElementById('productModal');
    const closeBtn    = modal.querySelector('.modal-close');
    const variantBody = document.getElementById('variantBody');

    document.querySelectorAll('.btn-detail').forEach(btn => {
        btn.addEventListener('click', function(){
            
            // Tampilkan modal & loading row
            modal.classList.add('active');
            variantBody.innerHTML = `<tr><td colspan="3" style="padding:8px;text-align:center;color:#888;">Memuat data...</td></tr>`;

            // 🔹 Ambil varian via AJAX
            fetch(`/productdetail/${this.dataset.id}`)
                .then(res => res.json())
                .then(data => {

                    // 🔹 SET HEADER MODAL REALTIME
                    document.getElementById('modalName').textContent          = data.name;
                    document.getElementById('modalPrice').textContent         = formatCurrency(data.price);
                    document.getElementById('modalStockProduct').textContent  = data.stock_product;
                    document.getElementById('modalStock').textContent         = data.stock_total;
                    document.getElementById('modalVariants').textContent      = data.variants_count;
                    document.getElementById('modalSales').textContent         = formatCurrency(data.total_revenue);
                    document.getElementById('modalSold').textContent          = data.total_qty_sold;
                    
                    // 🔹 VARIATION TABLE
                    if(data.variations && data.variations.length){
                        let rows = '';
                        data.variations.forEach(v => {
                            rows += `
                            <tr>
                                <td style="padding:6px;">${v.name}</td>
                                <td style="padding:6px;text-align:right;">${formatCurrency(v.price)}</td>
                                <td style="padding:6px;text-align:right;">${v.stock}</td>
                                <td style="padding:6px;text-align:right;">${v.sold ?? 0}</td>
                            </tr>`;
                        });
                        variantBody.innerHTML = rows;
                    } else {
                        variantBody.innerHTML = `<tr><td colspan="4" style="padding:8px;text-align:center;color:#888;">Tidak ada varian</td></tr>`;
                    }

                })
        });
    });

    closeBtn.addEventListener('click', () => modal.classList.remove('active'));
    modal.addEventListener('click', e => { if(e.target === modal) modal.classList.remove('active'); });

    function formatCurrency(value){
        return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR'}).format(value);
    }
});

</script>


@endsection