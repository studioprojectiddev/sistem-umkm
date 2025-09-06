@extends('layouts.app')

@section('title', 'POS (Kasir)')

@section('content')

{{-- Tambahkan ke dalam <style> atau file CSS --}}
<style>
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
            <button class="tab-link" data-target="integrasitransaksi">Integrasi Transaksi & Update Stok</button>
            <button class="tab-link" data-target="laporanproduk">Laporan & Analitik Produk</button>
            <button class="tab-link" data-target="supportfitur">Support Fitur Tambahan</button>
        </div>

        {{-- Tab: Identitas Produk --}}
        <div id="identitasproduk" class="tab-pane active">
            <h3>Identitas Produk Lengkap</h3>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Thumbnail</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>SKU / Barcode</th>
                        <th>Harga</th>
                        <th>Stok / Unit</th>
                        <th>Aksi Kasir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $index => $product)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if($product->thumbnail)
                                <img src="{{ asset($product->thumbnail) }}" 
                                     alt="{{ $product->name }}" width="50">
                            @else
                                <span>-</span>
                            @endif
                        </td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name ?? '-' }}</td>
                        <td>{{ $product->sku ?? $product->barcode ?? '-' }}</td>
                        <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td>{{ $product->stock }} {{ $product->unit ?? 'pcs' }}</td>
                        <td>
                            <button class="btn btn-sm btn-success btn-add-cart" 
                                    data-id="{{ $product->id }}">
                                + Keranjang
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Belum ada produk</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tab: Harga & Diskon --}}
        <div id="hargadiskon" class="tab-pane">
            <h3>Harga & Variasi Diskon</h3>
            <p>Belum ada konten. (Akan diisi setelah identitas produk selesai)</p>
        </div>

        {{-- Tab: Stok Real-time --}}
        <div id="stokreal" class="tab-pane">
            <h3>Stok Real-time & Unit</h3>
            <p>Belum ada konten.</p>
        </div>

        {{-- Tab: Aksi Kasir --}}
        <div id="aksikasir" class="tab-pane">
            <h3>Aksi Kasir (Keranjang)</h3>
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
                        <td>{{ $item['name'] }}</td>
                        <td><input type="number" value="{{ $item['quantity'] }}" class="form-control qty"></td>
                        <td>Rp {{ number_format($item['subtotal'],0,',','.') }}</td>
                        <td><button class="btn btn-danger btn-sm remove">X</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:10px;text-align:right;">
                <button class="btn btn-success" id="checkout">Checkout</button>
                <button class="btn btn-warning" id="clearCart">Kosongkan</button>
            </div>    
        </div>

        {{-- Tab: Integrasi Transaksi --}}
        <div id="integrasitransaksi" class="tab-pane">
            <h3>Integrasi Transaksi & Update Stok</h3>
            <p>Belum ada konten.</p>
        </div>

        {{-- Tab: Laporan Produk --}}
        <div id="laporanproduk" class="tab-pane">
            <h3>Laporan & Analitik Produk</h3>
            <p>Belum ada konten.</p>
        </div>

        {{-- Tab: Support Fitur --}}
        <div id="supportfitur" class="tab-pane">
            <h3>Support Fitur Tambahan</h3>
            <p>Belum ada konten.</p>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function refreshCart(cart){
    let tbody = $("#cartTable tbody");
    tbody.empty();
    Object.keys(cart).forEach(id => {
        let item = cart[id];
        tbody.append(`
            <tr data-id="${id}">
                <td>${item.name}</td>
                <td><input type="number" value="${item.quantity}" class="form-control qty"></td>
                <td>Rp ${item.subtotal.toLocaleString()}</td>
                <td><button class="btn btn-danger btn-sm remove">X</button></td>
            </tr>
        `);
    });
}

// Tambah produk
$(".btn-add-cart").click(function(){
    console.log('test')
    let id = $(this).data("id");
    $.post(`/umkm/pos/add/${id}`, {_token:"{{ csrf_token() }}"}, function(res){
        if(res.status === "success"){ refreshCart(res.cart); }
    });
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
    $.post(`/umkm/pos/checkout`, {_token:"{{ csrf_token() }}"}, function(res){
        alert(res.message);
        if(res.status === "success"){ refreshCart([]); }
    });
});
</script>
@endsection