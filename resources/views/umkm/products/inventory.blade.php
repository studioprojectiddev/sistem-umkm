@extends('layouts.app')

@section('title', 'Pembelian Stock')

@section('content')

<h1 class="title">📦 Pembelian Stock</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.product.inventory') }}" class="active">Pembelian Stock</a></li>
</ul>

<style>
.card-box{
    background:#fff;
    padding:20px;
    border-radius:16px;
    box-shadow:0 6px 20px rgba(0,0,0,0.05);
    margin-top:20px;
}

.summary-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
}

.summary-card{
    padding:18px;
    border-radius:14px;
    background:#f8f9fc;
}

.summary-card h2{
    margin-top:6px;
    font-size:1.5rem;
}

.text-success{color:#1cc88a;}
.text-danger{color:#e74a3b;}

.tab-header{
    display:flex;
    gap:10px;
    margin-bottom:15px;
}

.tab-btn{
    padding:8px 16px;
    border-radius:20px;
    border:none;
    cursor:pointer;
    background:#eee;
}

.tab-btn.active{
    background:#4e73df;
    color:white;
}

.table{
    width:100%;
    border-collapse:collapse;
}

.table th, .table td{
    padding:10px;
    border-bottom:1px solid #eee;
}

.table th{
    background:#f8f9fc;
}

.badge{
    padding:4px 10px;
    border-radius:6px;
    font-size:0.8rem;
}

.badge-success{
    background:#e6f9f2;
    color:#1cc88a;
}

.badge-warning{
    background:#fff4e5;
    color:#f6c23e;
}

.badge-danger{
    background:#fdecea;
    color:#e74a3b;
}

/* MODAL BACKDROP */
.modal {
    display: none;
    position: fixed;
    z-index: 999;
    inset: 0;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(3px);
}

/* MODAL BOX */
.modal-content {
    background: #fff;
    width: 400px;
    margin: 8% auto;
    border-radius: 12px;
    overflow: hidden;
    animation: fadeIn 0.3s ease;
}

/* HEADER */
.modal-header {
    padding: 16px 20px;
    background: #f8fafc;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
}

.close {
    cursor: pointer;
    font-size: 20px;
}

/* BODY */
.modal-body {
    padding: 20px;
}

/* INFO BOX */
.info-box {
    background: #f1f5f9;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 20px;
}

.info-box h2 {
    color: #e11d48;
    margin: 5px 0 0;
}

/* FORM */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-size: 13px;
    margin-bottom: 5px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ddd;
    outline: none;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #6366f1;
}

/* FOOTER */
.modal-footer {
    padding: 15px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid #eee;
}

.btn-cancel {
    background: #e5e7eb;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    cursor: pointer;
}

.btn-submit {
    background: #6366f1;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    cursor: pointer;
}

/* ANIMATION */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px);}
    to { opacity: 1; transform: translateY(0);}
}

.btn-pay{
    background: linear-gradient(135deg,#4e73df,#6f8cff);
    color:white;
    border:none;
    padding:6px 14px;
    border-radius:8px;
    cursor:pointer;
    font-size:13px;
    font-weight:500;
    transition: all 0.25s ease;
}

.btn-pay:hover{
    transform:translateY(-1px);
    box-shadow:0 6px 12px rgba(78,115,223,0.3);
}

.btn-pay:active{
    transform:scale(0.97);
}

.custom-pagination{
    display:flex;
    justify-content:center;
    gap:6px;
    margin-top:20px;
    flex-wrap:wrap;
}

.page{
    min-width:34px;
    height:34px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:8px;
    font-size:14px;
    text-decoration:none;
    color:#555;
    background:#f3f4f6;
    transition: all 0.2s ease;
}

/* Hover */
.page:hover{
    background:#4e73df;
    color:white;
}

/* Active */
.page.active{
    background:#4e73df;
    color:white;
    font-weight:600;
    box-shadow:0 4px 10px rgba(78,115,223,0.3);
}

/* Disabled */
.page.disabled{
    background:#e5e7eb;
    color:#aaa;
    pointer-events:none;
}

</style>

{{-- ================= SUMMARY ================= --}}
<div class="card-box">
    <div class="summary-grid">

        <div class="summary-card">
            <small>Total Pembelian</small>
            <h2 class="text-success">
                Rp{{ number_format($totalPurchase ?? 0,0,',','.') }}
            </h2>
        </div>

        <div class="summary-card">
            <small>Total Hutang</small>
            <h2 class="text-danger">
                Rp{{ number_format($totalDebt ?? 0,0,',','.') }}
            </h2>
        </div>

    </div>
</div>

{{-- ================= TAB ================= --}}
<div class="card-box">

    <div class="tab-header">
        <button class="tab-btn active" onclick="switchTab('purchase')">Riwayat Pembelian</button>
        <button class="tab-btn" onclick="switchTab('debt')">Hutang Supplier</button>
    </div>

    {{-- ================= RIWAYAT ================= --}}
    <div id="tab-purchase">

        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Total</th>
                    <th>Dibayar</th>
                    <th>Sisa</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($purchases as $p)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d M Y') }}</td>
                    <td>{{ $p->product_name }}</td>
                    <td>{{ $p->quantity }}</td>
                    <td>Rp{{ number_format($p->price,0,',','.') }}</td>
                    <td>
                        <strong>
                            Rp{{ number_format($p->total,0,',','.') }}
                        </strong>
                    </td>
                    <td style="color:#1cc88a; font-weight:600;">
                        Rp{{ number_format($p->paid ?? 0,0,',','.') }}
                    </td>
                    <td style="color:#e74a3b; font-weight:600;">
                        Rp{{ number_format($p->remaining ?? 0,0,',','.') }}
                    </td>
                    <td>
                        @if($p->payment_status == 'paid')
                            <span class="badge badge-success">Lunas</span>
                        @elseif($p->payment_status == 'partial')
                            <span class="badge badge-warning">Sebagian</span>
                        @else
                            <span class="badge badge-danger">Hutang</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;">Tidak ada data</td>
                </tr>
                @endforelse
            </tbody>

        </table>

        <div style="margin-top:15px;">
            @if ($purchases->lastPage() > 1)
                <div class="custom-pagination">

                    {{-- Prev --}}
                    @if ($purchases->onFirstPage())
                        <span class="page disabled">«</span>
                    @else
                        <a href="{{ $purchases->previousPageUrl() }}" class="page">«</a>
                    @endif

                    {{-- Numbers --}}
                    @for ($i = 1; $i <= $purchases->lastPage(); $i++)
                        @if ($i == $purchases->currentPage())
                            <span class="page active">{{ $i }}</span>
                        @else
                            <a href="{{ $purchases->url($i) }}" class="page">{{ $i }}</a>
                        @endif
                    @endfor

                    {{-- Next --}}
                    @if ($purchases->hasMorePages())
                        <a href="{{ $purchases->nextPageUrl() }}" class="page">»</a>
                    @else
                        <span class="page disabled">»</span>
                    @endif

                </div>
                @endif
        </div>

    </div>

    {{-- ================= HUTANG ================= --}}
    <div id="tab-debt" style="display:none;">

        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Supplier</th>
                    <th>Sisa Hutang</th>
                    <th>Jatuh Tempo</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @forelse($debts as $d)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($d->created_at)->format('d M Y') }}</td>
                    <td>{{ $d->product_name }}</td>
                    <td>{{ $d->supplier_name ?? '-' }}</td>
                    <td class="text-danger">
                        Rp{{ number_format($d->remaining,0,',','.') }}
                    </td>
                    <td>
                        {{ $d->due_date ? \Carbon\Carbon::parse($d->due_date)->format('d M Y') : '-' }}
                    </td>
                    <td>
                        <button 
                            class="btn-pay"
                            onclick="openModal({{ $d->id }}, {{ $d->remaining }})">
                            💳 Bayar
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;">Tidak ada hutang</td>
                </tr>
                @endforelse
            </tbody>

        </table>

        <div style="margin-top:15px;">
            {{ $debts->links() }}
        </div>

    </div>

</div>

<div id="modalBayar" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>💳 Bayar Hutang</h3>
            <span class="close" onclick="closeModal()">×</span>
        </div>

        <div class="modal-body">
            <div class="info-box">
                <p>Sisa Hutang</p>
                <h2 id="sisaHutang">Rp 0</h2>
            </div>

            <div class="form-group">
                <label>Jumlah Bayar</label>
                <input type="number" id="jumlahBayar" placeholder="Masukkan jumlah pembayaran">
            </div>

            <div class="form-group">
                <label>Pilih Rekening</label>
                <select id="accountId">
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal()">Batal</button>
            <button class="btn-submit" onclick="submitBayar()">Bayar Sekarang</button>
        </div>
    </div>
</div>

<script>
function switchTab(tab){
    document.getElementById('tab-purchase').style.display = 'none';
    document.getElementById('tab-debt').style.display = 'none';

    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

    if(tab === 'purchase'){
        document.getElementById('tab-purchase').style.display = 'block';
        document.querySelectorAll('.tab-btn')[0].classList.add('active');
    }else{
        document.getElementById('tab-debt').style.display = 'block';
        document.querySelectorAll('.tab-btn')[1].classList.add('active');
    }
}
</script>
<script>
let currentLogId = null;

function openModal(logId, remaining) {
    currentLogId = logId;

    document.getElementById('modalBayar').style.display = 'block';
    document.getElementById('sisaHutang').innerText = formatRupiah(remaining);
    document.getElementById('jumlahBayar').value = remaining;
}

function closeModal() {
    document.getElementById('modalBayar').style.display = 'none';
}

function formatRupiah(angka) {
    return 'Rp' + new Intl.NumberFormat('id-ID').format(angka);
}

function submitBayar(){

    let amount = document.getElementById('jumlahBayar').value;
    let account = document.getElementById('accountId').value;

    if(!amount || amount <= 0){
        Swal.fire({
            icon:'warning',
            title:'Oops...',
            text:'Masukkan jumlah bayar'
        });
        return;
    }

    fetch("{{ route('umkm.inventory.payDebt') }}", {
        method:'POST',
        headers:{
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body:new URLSearchParams({
            log_id: currentLogId,
            amount: amount,
            account_id: account
        })
    })
    .then(res => res.json())
    .then(res => {

        if(res.status === 'success'){

            Swal.fire({
                icon:'success',
                title:'Berhasil',
                text:'Pembayaran hutang berhasil',
                confirmButtonColor:'#4e73df'
            }).then(()=>{
                location.reload();
            });

        }else{

            Swal.fire({
                icon:'error',
                title:'Gagal',
                text:res.message
            });

        }

    })
    .catch(err => {
        console.error(err);

        Swal.fire({
            icon:'error',
            title:'Error',
            text:'Terjadi kesalahan sistem'
        });
    });
}
</script>

@endsection