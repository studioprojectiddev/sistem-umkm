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
                        {{ $product->name }}
                        @if($product->variations->count())
                            <ul class="mb-0 ps-3">
                                @foreach($product->variations as $index => $var)
                                    <li>
                                        {{ $index + 1 }}.  
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
                        @endif
                    </td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->sku ?? $product->barcode ?? '-' }}</td>
                    <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                    <td>{{ $product->stock }} {{ $product->unit ?? 'pcs' }}</td>
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
        </div>

        {{-- Tab: Harga & Diskon --}}
        <div id="hargadiskon" class="tab-pane">
            <div class="form-card">
                <h3 class="form-title">💰 Harga & Variasi Diskon</h3>

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

                <form id="discountForm" method="POST" action="">
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

                    <div class="form-group full">
                        <label for="final_price">Harga Setelah Diskon (Rp)</label>
                        <input type="text" id="final_price" name="discount_price" readonly>
                    </div><br>

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
            <h3>Stok Real-time & Unit</h3>
            <p>Belum ada konten.</p>
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
$(document).on('click', '.btn-add-cart', function(e){
    e.preventDefault();
    let $btn = $(this);
    let productId = $btn.data('id');
    let hasVariation = $btn.data('has-variation') == 1;
    let variations = $btn.data('variations') || [];
    let productPrice = $btn.data('price') || 0;

    if(hasVariation){
        let html = `<select id="swal_select" class="swal2-select" style="width:100%;padding:8px;">
            <option value="product_${productId}">Produk Utama - Rp ${Number(productPrice).toLocaleString()}</option>`;

        variations.forEach(v => {
            html += `<option value="variation_${v.id}">${v.name} - Rp ${Number(v.price||productPrice).toLocaleString()} (Stok: ${v.stock})</option>`;
        });
        html += `</select>`;

        Swal.fire({
            title: 'Pilih produk / varian',
            html: html,
            showCancelButton: true,
            confirmButtonText: 'Tambah ke Keranjang',
            preConfirm: () => {
                return $('#swal_select').val();
            }
        }).then(result => {
            if(result.isConfirmed && result.value){
                const val = result.value;
                if(val.startsWith('product_')){
                    const id = val.split('_')[1];
                    $.post(`/umkm/pos/add/${id}`, {_token: "{{ csrf_token() }}"}, function(res){
                        if(res.status === 'success') refreshCart(res.cart);
                        else Swal.fire('Error', res.message || 'Gagal menambah', 'error');
                    });
                } else {
                    const vid = val.split('_')[1];
                    $.post(`/umkm/pos/add-variation/${vid}`, {_token: "{{ csrf_token() }}"}, function(res){
                        if(res.status === 'success') refreshCart(res.cart);
                        else Swal.fire('Error', res.message || 'Gagal menambah', 'error');
                    });
                }
            }
        });
    } else {
        // no variation - add product
        $.post(`/umkm/pos/add/${productId}`, {_token: "{{ csrf_token() }}"}, function(res){
            if(res.status === 'success') refreshCart(res.cart);
            else Swal.fire('Error', res.message || 'Gagal menambah', 'error');
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
    $.post(`/umkm/pos/checkout`, {_token:"{{ csrf_token() }}"}, function(res){
        if(res.status === "success"){ 
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: res.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                refreshCart([]);
                $.get(`/umkm/pos/products`, function(products){
                    let tbody = $("table tbody");
                    tbody.empty();
                    products.forEach((p, index) => {
                        tbody.append(`
                            <tr>
                                <td>${index+1}</td>
                                <td>${p.thumbnail ? `<img src="/${p.thumbnail}" width="50">` : '-'}</td>
                                <td>${p.name}</td>
                                <td>-</td>
                                <td>${p.sku ?? '-'}</td>
                                <td>Rp ${p.price.toLocaleString()}</td>
                                <td>${p.stock} ${p.unit ?? 'pcs'}</td>
                                <td><button class="btn btn-sm btn-success btn-add-cart" data-id="${p.id}">+ Keranjang</button></td>
                            </tr>
                        `);
                    });
                });
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: res.message
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

</script>
@endsection