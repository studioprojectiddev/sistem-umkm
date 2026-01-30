@extends('layouts.kasir')

@section('content')
<style>
.kasir-container {
    display: grid;
    grid-template-columns: 3fr 1.2fr;
    height: 100vh;
    background: #f4f6fb;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 14px;
}

.product-btn {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e5e7eb;
    padding: 16px;
    min-height: 130px;
    text-align: left;

    display: flex;
    flex-direction: column;
    justify-content: space-between;

    transition: all .18s ease;
    box-shadow: 0 4px 14px rgba(0,0,0,.04);
}

.product-btn:hover {
    border-color: #2563eb;
    box-shadow: 0 10px 24px rgba(37,99,235,.18);
    transform: translateY(-2px);
}

.product-btn:active {
    transform: scale(.97);
}

.product-btn strong {
    font-size: 15px;
    font-weight: 600;
    color: #111827;
}

.product-btn .price {
    font-size: 14px;
    font-weight: 600;
    color: #2563eb;
}

.product-btn .stock {
    font-size: 12px;
    color: #6b7280;
}

.kasir-cart {
    background: #ffffff;
    padding: 18px;
    display: flex;
    flex-direction: column;
    border-left: 1px solid #e5e7eb;
}

.kasir-cart h3 {
    margin-bottom: 12px;
}

.cart-list {
    flex: 1;
    overflow-y: auto;
}

.cart-item {
    border-bottom: 1px dashed #e5e7eb;
    padding: 10px 0;
}

.cart-item strong {
    font-size: 14px;
}

.cart-item small {
    color: #6b7280;
}

.cart-footer {
    border-top: 1px solid #e5e7eb;
    padding-top: 12px;
}

.cart-total {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 10px;
}

.btn-bayar {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    border: none;
    color: white;
    font-size: 18px;
    padding: 14px;
    border-radius: 14px;
    font-weight: 600;
}

.btn-bayar:active {
    transform: scale(.97);
}

button, input {
    touch-action: manipulation;
}

input[type=number] {
    font-size: 18px;
    height: 44px;
}

.category-bar {
    display: flex;
    gap: 10px;
    margin-bottom: 14px;
    overflow-x: auto;
    padding-bottom: 6px;
}

.category-chip {
    background: #f1f5f9;
    border: none;
    border-radius: 999px;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 600;
    white-space: nowrap;
    cursor: pointer;
    transition: all .15s ease;
}

.category-chip i {
    margin-right: 6px;
}

.category-chip.active {
    background: #2563eb;
    color: white;
}

.category-chip:active {
    transform: scale(.95);
}

.kasir-search {
    margin-bottom: 14px;
}

.kasir-search input {
    width: 100%;
    padding: 14px 18px;
    font-size: 18px;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    outline: none;
}

.kasir-search input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.2);
}

.payment-modal {
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,.55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.payment-modal.hidden {
    display: none;
}

.payment-box {
    background: #fff;
    width: 100%;
    max-width: 420px;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 20px 40px rgba(0,0,0,.25);
}

.payment-box h2 {
    text-align: center;
    margin-bottom: 16px;
}

.payment-total {
    text-align: center;
    font-size: 16px;
    margin-bottom: 20px;
}

.payment-total strong {
    display: block;
    font-size: 26px;
    margin-top: 4px;
}

.payment-input label {
    font-size: 14px;
    color: #374151;
}

.payment-input input {
    width: 100%;
    height: 56px;
    font-size: 22px;
    padding: 0 14px;
    margin-top: 6px;
    border-radius: 14px;
    border: 1px solid #d1d5db;
}

.payment-change {
    text-align: center;
    margin: 18px 0;
    font-size: 16px;
}

.payment-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.btn-cancel {
    background: #e5e7eb;
    border: none;
    border-radius: 14px;
    padding: 14px;
    font-size: 16px;
}

.btn-confirm {
    background: linear-gradient(135deg,#2563eb,#1d4ed8);
    color: white;
    border: none;
    border-radius: 14px;
    padding: 14px;
    font-size: 18px;
    font-weight: 600;
}

.btn-confirm:active,
.btn-cancel:active {
    transform: scale(.97);
}   

.qty-control {
    display: flex;
    align-items: center;
    gap: 6px;
}

.qty-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: none;
    background: #e5e7eb;
    font-size: 20px;
    font-weight: bold;
}

.qty-input {
    width: 60px;
    text-align: center;
    font-size: 18px;
}

.btn-remove:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.qty-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.qty-input:disabled {
    background: #f1f5f9;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.spinner {
    width:28px;
    height:28px;
    border:3px solid #e5e7eb;
    border-top-color:#2563eb;
    border-radius:50%;
    animation: spin .8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.toast {
    min-width:260px;
    padding:14px 18px;
    border-radius:14px;
    color:white;
    font-size:15px;
    font-weight:600;
    box-shadow:0 12px 24px rgba(0,0,0,.2);
    animation: slideIn .3s ease, fadeOut .3s ease 2.7s forwards;
}

.toast.success { background:#16a34a; }
.toast.error   { background:#dc2626; }
.toast.warn    { background:#f59e0b; color:#111827; }

@keyframes slideIn {
    from { opacity:0; transform:translateX(30px); }
    to   { opacity:1; transform:translateX(0); }
}

@keyframes fadeOut {
    to { opacity:0; transform:translateX(30px); }
}

.cart-item.active {
    background: #eef2ff;
    border-left: 4px solid #2563eb;
    padding-left: 10px;
}

.product-btn.disabled {
    opacity: .4;
    pointer-events: none;
    filter: grayscale(1);
}

.stock-warning {
    color: #dc2626;
    font-weight: 700;
}

.cart-item.stock-empty {
    opacity: .5;
}

.cart-item.stock-warning {
    background: #fff7ed;
}

.payment-row {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 8px;
    margin-bottom: 8px;
}

.pay-method, .pay-amount {
    height: 48px;
    border-radius: 12px;
    border: 1px solid #d1d5db;
    padding: 0 10px;
}

</style>

<div class="kasir-container">

    {{-- PRODUK --}}
    <div class="kasir-products">
        <h3>🛒 Produk</h3>
        {{-- SEARCH PRODUK --}}
        <div class="kasir-search">
            <input 
                type="text" 
                id="searchProduct"
                placeholder="Cari produk..."
                autocomplete="off"
            >
        </div>

        {{-- CATEGORY BAR --}}
        <div class="category-bar">
            <button class="category-chip active" data-category="all">
                Semua
            </button>

            @foreach($categories as $cat)
                <button class="category-chip"
                        data-category="{{ $cat->id }}">
                    @if($cat->icon)
                        <i class="{{ $cat->icon }}"></i>
                    @endif
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        <div class="product-grid">
            @foreach($products as $product)
            <button class="product-btn btn-add-cart"
                data-id="{{ $product->id }}"
                data-category-parent="{{ optional($product->category)->parent_id ?? optional($product->category)->id ?? 'uncategorized' }}"
                data-name="{{ strtolower($product->name) }}"
                data-sku="{{ strtolower($product->sku ?? '') }}">

                <strong>{{ $product->name }}</strong>

                <div>
                    <div class="price">
                        Rp {{ number_format($product->final_price,0,',','.') }}
                    </div>
                    <div class="stock {{ $product->stockProduct <= 5 ? 'stock-warning' : '' }}">
                        Stok: {{ $product->stockProduct }}
                    </div>
                </div>
            </button>
            @endforeach
        </div>
    </div>

    {{-- CART --}}
    <div class="kasir-cart">
        <h3>🧾 Keranjang</h3>

        <div id="emptyCart"
            style="
                display:none;
                text-align:center;
                padding:40px 20px;
                color:#6b7280;
                animation: fadeIn .25s ease;
            ">
            <div style="font-size:48px;">🛒</div>
            <p style="margin-top:12px; font-size:15px;">
                Keranjang masih kosong
            </p>
            <small>Silakan pilih produk</small>
        </div>


        <div class="cart-list" id="cartList">
            @foreach($cart as $item)
                <div class="cart-item" data-key="{{ $item['key'] }}">
                    <strong>{{ $item['name'] }}</strong>
                    @if($item['variation'])
                        <br><small>{{ $item['variation'] }}</small>
                    @endif

                    <div style="display:flex; justify-content:space-between; margin-top:6px;">
                        <div class="qty-control" data-key="{{ $item['key'] }}">
                            <button class="qty-btn minus">−</button>

                            <input type="number"
                                class="qty-input"
                                value="{{ $item['quantity'] }}"
                                min="1">

                            <button class="qty-btn plus">+</button>
                        </div>

                        <span>x{{ $item['quantity'] }}</span>
                        <span class="item-subtotal">
                            Rp {{ number_format($item['subtotal'],0,',','.') }}
                        </span>
                    </div>

                    <button class="btn-remove"
                            data-key="{{ $item['key'] }}">
                        ❌
                    </button>
                </div>
            @endforeach
        </div>

        <div class="cart-footer">
            <div class="cart-total">
                Total: <span id="cartTotal">
                    Rp {{ number_format(collect($cart)->sum('subtotal'),0,',','.') }}
                </span>
            </div>

            <button class="btn-bayar" id="btnBayar">💳 Bayar</button>
        </div>

    </div>

</div>

<div id="paymentModal" class="payment-modal hidden">

    <div class="payment-methods">

    <div class="payment-row">
        <select class="pay-method">
            <option value="cash">Cash</option>
            <option value="qris">QRIS</option>
            <option value="transfer">Transfer</option>
        </select>

        <input type="number"
            class="pay-amount"
            placeholder="Jumlah">
    </div>

    <button id="addPaymentRow">+ Tambah Metode</button>

    </div>

    <div class="payment-change">
        Sisa:
        <strong id="paymentRemaining">Rp 0</strong>
    </div>

</div>

<div id="qtyModal" class="payment-modal hidden">
    <div class="payment-box">

        <h2>🧮 Jumlah</h2>

        <div class="payment-input">
            <label>Masukkan Jumlah</label>
            <input type="number" id="qtyInput" value="1" min="1">
        </div>

        <div class="payment-actions">
            <button class="btn-cancel" id="qtyCancel">Batal</button>
            <button class="btn-confirm" id="qtyConfirm">Tambah</button>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // =============================
    // FILTER PRODUK
    // =============================
    let activeCategory = 'all';

    document.querySelectorAll('.category-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            document.querySelectorAll('.category-chip')
                .forEach(c => c.classList.remove('active'));

            this.classList.add('active');
            activeCategory = this.dataset.category;

            filterProducts();
        });
    });

    const searchInput = document.getElementById('searchProduct');
    if (searchInput) {
        searchInput.addEventListener('input', filterProducts);
        searchInput.focus();
    }

    function filterProducts() {
        const keyword = searchInput.value.toLowerCase().trim();

        document.querySelectorAll('.product-btn').forEach(prod => {
            const name = prod.dataset.name || '';
            const sku  = prod.dataset.sku || '';
            const cat  = prod.dataset.categoryParent;

            const matchSearch = !keyword || name.includes(keyword) || sku.includes(keyword);
            const matchCategory =
                activeCategory === 'all' ||
                String(cat) === String(activeCategory);

            prod.style.display = (matchSearch && matchCategory)
                ? 'flex'
                : 'none';
        });
    }

    document.addEventListener('keydown', function(e){
        if(e.key === 'Escape' && searchInput){
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        }
    });

    // =============================
    // MODAL PEMBAYARAN
    // =============================
    // const modal = document.getElementById('paymentModal');
    // const btnBayar = document.getElementById('btnBayar');
    // const btnCancel = document.getElementById('cancelPayment');
    // const uangInput = document.getElementById('uangDiterima');
    // const kembalianText = document.getElementById('kembalianText');
    // const btnConfirm = document.getElementById('confirmPayment');

    // const totalBayar = {{ collect($cart)->sum('subtotal') }};

    // if (btnBayar) {
    //     btnBayar.addEventListener('click', () => {
    //         modal.classList.remove('hidden');
    //         uangInput.value = '';
    //         kembalianText.innerText = 'Rp 0';
    //         kembalianText.style.color = '#000';
    //         uangInput.focus();
    //     });
    // }

    // if (btnCancel) {
    //     btnCancel.addEventListener('click', () => {
    //         modal.classList.add('hidden');
    //     });
    // }

    // if (uangInput) {
    //     uangInput.addEventListener('input', function () {
    //         const uang = parseInt(this.value || 0);
    //         const kembali = uang - totalBayar;

    //         if (kembali < 0) {
    //             kembalianText.innerText = 'Uang Kurang';
    //             kembalianText.style.color = 'red';
    //         } else {
    //             kembalianText.innerText = 'Rp ' + kembali.toLocaleString('id-ID');
    //             kembalianText.style.color = 'green';
    //         }
    //     });
    // }

    // if (btnConfirm) {
    //     btnConfirm.addEventListener('click', function () {

    //         const payments = [];
    //         let totalPaid = 0;

    //         document.querySelectorAll('.payment-row').forEach(row => {
    //             const method = row.querySelector('.pay-method').value;
    //             const amount = parseInt(
    //                 row.querySelector('.pay-amount').value || 0
    //             );

    //             if (amount > 0) {
    //                 payments.push({ method, amount });
    //                 totalPaid += amount;
    //             }
    //         });

    //         if (totalPaid !== totalBayar) {
    //             alert('Total pembayaran belum sesuai');
    //             return;
    //         }

    //         fetch('/kasir/checkout', {
    //             method: 'POST',
    //             headers: {
    //                 'Content-Type': 'application/json',
    //                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
    //             },
    //             body: JSON.stringify({
    //                 total,
    //                 payments
    //             })
    //         })
    //         .then(r => r.json())
    //         .then(res => {
    //             if (res.status === 'success') {
    //                 alert('Transaksi berhasil');
    //                 location.reload();
    //             } else {
    //                 alert(res.message);
    //             }
    //         });
    //     });

    // }

});
</script>
<!-- <script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.btn-add-cart').forEach(btn => {
        btn.addEventListener('click', function () {

            const productId = this.dataset.id;

            fetch(`/kasir/add/${productId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    location.reload(); // versi aman dulu
                } else {
                    toast(res.message || 'Gagal tambah produk');
                }
            });

        });
    });

});
</script> -->
<script>
let selectedProductId = null;

const qtyModal   = document.getElementById('qtyModal');
const qtyInput   = document.getElementById('qtyInput');
const qtyCancel  = document.getElementById('qtyCancel');
const qtyConfirm = document.getElementById('qtyConfirm');

document.querySelectorAll('.btn-add-cart').forEach(btn => {
    btn.addEventListener('click', function () {
        selectedProductId = this.dataset.id;
        qtyInput.value = 1;
        qtyModal.classList.remove('hidden');
        setTimeout(() => qtyInput.focus(), 100);
    });
});

qtyCancel.addEventListener('click', () => {
    qtyModal.classList.add('hidden');
    selectedProductId = null;
});

qtyConfirm.addEventListener('click', submitQty);

// tekan ENTER = submit
qtyInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') submitQty();
});

function rupiah(n) {
    return 'Rp ' + n.toLocaleString('id-ID');
}

let optimisticLock = false;

function renderCartItem(item) {
    const cartList = document.getElementById('cartList');

    let row = cartList.querySelector(
        `.cart-item[data-key="${item.key}"]`
    );

    if (row) {
        // update qty & subtotal
        row.querySelector('.qty-input').value = item.quantity;
        row.querySelector('.item-subtotal').innerText = rupiah(item.subtotal);
        row.querySelector('span').innerText = 'x' + item.quantity;
    } else {
        // item baru
        row = document.createElement('div');
        row.className = 'cart-item';
        row.dataset.key = item.key;

        row.innerHTML = `
            <strong>${item.name}</strong>
            ${item.variation ? `<br><small>${item.variation}</small>` : ''}

            <div style="display:flex; justify-content:space-between; margin-top:6px;">
                <div class="qty-control" data-key="${item.key}">
                    <button class="qty-btn minus">−</button>
                    <input type="number" class="qty-input" value="${item.quantity}" min="1">
                    <button class="qty-btn plus">+</button>
                </div>

                <span>x${item.quantity}</span>
                <span class="item-subtotal">${rupiah(item.subtotal)}</span>
            </div>

            <button class="btn-remove" data-key="${item.key}">❌</button>
        `;

        cartList.appendChild(row);
    }
}

function submitQty() {
    const qty = parseInt(qtyInput.value || 1);
    if (qty < 1) return toast('Jumlah minimal 1');

    showLoading();

    fetch(`/kasir/add/${selectedProductId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ qty })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {

            renderCartItem(res.item);

            document.getElementById('cartTotal').innerText =
                rupiah(res.total);

                const paymentTotalEl = document.getElementById('paymentTotal');
                if (paymentTotalEl) {
                    paymentTotalEl.innerText = rupiah(res.total);
                }

            checkEmptyCart();
            qtyModal.classList.add('hidden');

            toast(res.message || 'Berhasil menambah produk', 'success');

        } else {
            toast(res.message || 'Gagal menambah produk', 'error');
        }
    })
    .finally(() => {
        hideLoading();
    });
}
</script>
<script>
document.addEventListener('click', function(e) {

    if (!e.target.classList.contains('plus') &&
        !e.target.classList.contains('minus')) return;

    if (optimisticLock) return;
    optimisticLock = true;

    const ctrl  = e.target.closest('.qty-control');
    const key   = ctrl.dataset.key;
    const input = ctrl.querySelector('.qty-input');
    const row   = ctrl.closest('.cart-item');

    const oldQty = parseInt(input.value);
    let newQty   = oldQty;

    if (e.target.classList.contains('plus')) newQty++;
    if (e.target.classList.contains('minus') && newQty > 1) newQty--;

    if (newQty === oldQty) {
        optimisticLock = false;
        return;
    }

    /* ==========================
       🔮 OPTIMISTIC UI
    ========================== */
    input.value = newQty;
    row.querySelector('span').innerText = 'x' + newQty;

    const price = parseInt(
        row.querySelector('.item-subtotal')
           .innerText.replace(/[^\d]/g,'')
    ) / oldQty;

    row.querySelector('.item-subtotal').innerText =
        rupiah(price * newQty);

    /* ==========================
       🌐 SERVER REQUEST
    ========================== */
    fetch('/kasir/cart/update-qty', {
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body:JSON.stringify({ key, qty:newQty })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {

            document.getElementById('cartTotal').innerText =
                rupiah(res.total);

                const paymentTotalEl = document.getElementById('paymentTotal');
                if (paymentTotalEl) {
                    paymentTotalEl.innerText = rupiah(res.total);
                }

        } else {
            rollback();
            toast(res.message, 'error');
        }
    })
    .catch(() => {
        rollback();
        toast('Koneksi gagal', 'error');
    })
    .finally(() => optimisticLock = false);

    /* ==========================
       🔙 ROLLBACK
    ========================== */
    function rollback() {
        input.value = oldQty;
        row.querySelector('span').innerText = 'x' + oldQty;
        row.querySelector('.item-subtotal').innerText =
            rupiah(price * oldQty);
    }
});
</script>
<script>
document.addEventListener('click', function(e){

    if (!e.target.classList.contains('btn-remove')) return;

    if (optimisticLock) return;
    optimisticLock = true;

    const btn = e.target;
    const key = btn.dataset.key;
    const row = btn.closest('.cart-item');

    if (!confirm('Hapus item ini?')) {
        optimisticLock = false;
        return;
    }

    /* 🔮 Optimistic UI */
    row.style.opacity = .4;
    row.style.pointerEvents = 'none';

    fetch('/kasir/remove-item', {
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body:JSON.stringify({ key })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            row.remove();

            document.getElementById('cartTotal').innerText =
                rupiah(res.total);

                const paymentTotalEl = document.getElementById('paymentTotal');
                if (paymentTotalEl) {
                    paymentTotalEl.innerText = rupiah(res.total);
                }

            toast('Item dihapus', 'warn');

        } else {
            rollback();
            toast(res.message, 'error');
        }
    })
    .catch(() => {
        rollback();
        toast('Koneksi gagal', 'error');
    })
    .finally(() => optimisticLock = false);

    function rollback(){
        row.style.opacity = 1;
        row.style.pointerEvents = 'auto';
    }
});
</script>
<script>
function checkEmptyCart() {
    const cartList  = document.getElementById('cartList');
    const emptyCart = document.getElementById('emptyCart');
    const btnBayar  = document.getElementById('btnBayar');

    const hasItem = cartList.children.length > 0;

    emptyCart.style.display = hasItem ? 'none' : 'block';
    cartList.style.display  = hasItem ? 'block' : 'none';

    btnBayar.disabled = !hasItem;
    btnBayar.style.opacity = hasItem ? '1' : '.5';
}
</script>
<script>
function showLoading() {
    document.getElementById('globalLoading').style.display = 'flex';
    document.body.style.pointerEvents = 'none';
}

function hideLoading() {
    document.getElementById('globalLoading').style.display = 'none';
    document.body.style.pointerEvents = 'auto';
}
</script>

<script>
function toast(message, type = 'success') {
    const container = document.getElementById('toastContainer');

    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerText = message;

    container.appendChild(el);

    setTimeout(() => {
        el.remove();
    }, 3000);
}
</script>
<script>
document.addEventListener('click', function(e){
    const item = e.target.closest('.cart-item');
    if (!item) return;

    document.querySelectorAll('.cart-item')
        .forEach(i => i.classList.remove('active'));

    item.classList.add('active');
});
</script>
<script>
document.addEventListener('keydown', function(e){

    const active = document.querySelector('.cart-item.active');
    if (!active) return;

    const key = active.dataset.key;
    const plus  = active.querySelector('.plus');
    const minus = active.querySelector('.minus');
    const del   = active.querySelector('.btn-remove');

    switch (e.key) {

        case '+':
        case '=':
            plus?.click();
            e.preventDefault();
            break;

        case '-':
            minus?.click();
            e.preventDefault();
            break;

        case 'Delete':
            del?.click();
            e.preventDefault();
            break;

        case 'Enter':
            document.getElementById('btnBayar')?.click();
            e.preventDefault();
            break;

        case 'Escape':
            document.getElementById('paymentModal')?.classList.add('hidden');
            document.getElementById('qtyModal')?.classList.add('hidden');
            break;
    }
});
</script>
<script>
document.addEventListener('keydown', function(e){

    if (
        e.target.tagName === 'INPUT' ||
        e.ctrlKey || e.altKey
    ) return;

    if (/^[a-zA-Z0-9]$/.test(e.key)) {
        const search = document.getElementById('searchProduct');
        if (search) {
            search.focus();
            search.value += e.key;
        }
    }
});
</script>
<script>
document.addEventListener('click', function(e){

    if (!e.target.classList.contains('plus')) return;

    const ctrl  = e.target.closest('.qty-control');
    const item  = ctrl.closest('.cart-item');

    const stock = parseInt(item.dataset.stock);
    const input = ctrl.querySelector('.qty-input');
    const qty   = parseInt(input.value);

    if (qty >= stock) {
        alert('Stok tidak mencukupi');
        e.stopImmediatePropagation();
        return;
    }

    if (stock <= 0) {
        item.classList.add('stock-empty');
    } else if (stock <= 5) {
        item.classList.add('stock-warning');
    }

});
</script>
<script>
(function () {

window.totalBayar = {{ collect($cart)->sum('subtotal') }};

const container     = document.querySelector('.payment-methods');
const remainingText = document.getElementById('paymentRemaining');
const btnBayar      = document.getElementById('btnBayar');
const addPaymentBtn = document.getElementById('addPaymentRow');

if (!container || !remainingText || !btnBayar) return;

function rupiah(n) {
    return 'Rp ' + Number(n).toLocaleString('id-ID');
}

function calculateRemaining() {
    let paid = 0;

    document.querySelectorAll('.pay-amount').forEach(input => {
        const val = parseInt(input.value);
        if (!isNaN(val)) paid += val;
    });

    const remain = window.totalBayar - paid;

    const methods = document.querySelectorAll('.pay-method');
    const isCashOnly =
        methods.length === 1 && methods[0].value === 'cash';

    if (remain < 0 && isCashOnly) {
        remainingText.innerText =
            'Kembalian: ' + rupiah(Math.abs(remain));
        remainingText.style.color = 'green';
        btnBayar.disabled = false;
        return 0;
    }

    remainingText.innerText = rupiah(remain);
    remainingText.style.color =
        remain === 0 ? 'green' :
        remain < 0 ? 'red' : '#000';

    btnBayar.disabled = remain !== 0;
    return remain;
}

document.addEventListener('input', e => {
    if (e.target.classList.contains('pay-amount')) {
        calculateRemaining();
    }
});

addPaymentBtn?.addEventListener('click', () => {
    const row = document.createElement('div');
    row.className = 'payment-row';
    row.innerHTML = `
        <select class="pay-method">
            <option value="cash">Cash</option>
            <option value="qris">QRIS</option>
            <option value="transfer">Transfer</option>
        </select>
        <input type="number" class="pay-amount" min="0">
    `;
    container.appendChild(row);
});

function collectPayments() {
    const payments = [];
    document.querySelectorAll('.payment-row').forEach(row => {
        const method = row.querySelector('.pay-method').value;
        const amount = parseInt(row.querySelector('.pay-amount').value || 0);
        if (amount > 0) payments.push({ method, amount });
    });
    return payments;
}

btnBayar.addEventListener('click', () => {
    if (calculateRemaining() !== 0) return;

    fetch('/kasir/checkout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ payments: collectPayments() })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            alert('Transaksi berhasil');
            location.reload();
        } else {
            alert(res.message);
        }
    });
});

btnBayar.disabled = true;
})();

</script>
<!-- <script>
const total = {{ collect($cart)->sum('subtotal') }};

const container = document.querySelector('.payment-methods');
const remainingText = document.getElementById('paymentRemaining');
const btnBayar = document.getElementById('btnBayar');

function rupiah(n) {
    return 'Rp ' + n.toLocaleString('id-ID');
}

function calculateRemaining() {
    let paid = 0;

    document.querySelectorAll('.pay-amount').forEach(input => {
        const val = parseInt(input.value);
        if (!isNaN(val)) paid += val;
    });

    const remain = window.totalBayar - paid;

    // CASH MODE (boleh lebih bayar)
    const methods = document.querySelectorAll('.pay-method');
    const isCashOnly =
        methods.length === 1 && methods[0].value === 'cash';

    if (remain < 0 && isCashOnly) {
        remainingText.innerText =
            'Kembalian: ' + rupiah(Math.abs(remain));
        remainingText.style.color = 'green';
        btnBayar.disabled = false;
        return remain;
    }

    // NORMAL
    remainingText.innerText = rupiah(remain);
    remainingText.style.color =
        remain === 0 ? 'green' :
        remain < 0 ? 'red' : '#000';

    btnBayar.disabled = remain !== 0;
    return remain;
}

// realtime hitung saat input berubah
document.addEventListener('input', function(e){
    if (e.target.classList.contains('pay-amount')) {
        calculateRemaining();
    }
});

// tambah metode pembayaran
document.getElementById('addPaymentRow')
?.addEventListener('click', function(){

    const row = document.createElement('div');
    row.className = 'payment-row';

    row.innerHTML = `
        <select class="pay-method">
            <option value="cash">Cash</option>
            <option value="qris">QRIS</option>
            <option value="transfer">Transfer</option>
        </select>

        <input type="number"
               class="pay-amount"
               placeholder="Jumlah"
               min="0">
    `;

    container.appendChild(row);
});
</script> -->

<div id="globalLoading"
     style="
        position:fixed;
        inset:0;
        background:rgba(15,23,42,.55);
        display:none;
        align-items:center;
        justify-content:center;
        z-index:99999;
     ">
    <div style="
        background:#fff;
        padding:22px 26px;
        border-radius:16px;
        display:flex;
        align-items:center;
        gap:12px;
        box-shadow:0 20px 40px rgba(0,0,0,.25);
     ">
        <div class="spinner"></div>
        <strong>Memproses...</strong>
    </div>
</div>
<div id="toastContainer"
     style="
        position:fixed;
        top:20px;
        right:20px;
        display:flex;
        flex-direction:column;
        gap:10px;
        z-index:100000;
     ">
</div>

@endsection