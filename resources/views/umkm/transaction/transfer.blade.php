@extends('layouts.app')

@section('title', 'Transfer Antar Rekening')

@section('content')

<h1 class="title">💸 Transfer Antar Rekening</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li class="active">Transfer Antar Rekening</li>
</ul>

<style>
.card-box{
    background:#fff;
    padding:20px;
    border-radius:16px;
    box-shadow:0 6px 20px rgba(0,0,0,0.05);
    margin-top:20px;
}

.form-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
}

.form-control{
    padding:10px 12px;
    border-radius:10px;
    border:1px solid #ddd;
}

.form-control:focus{
    border-color:#4e73df;
    outline:none;
    box-shadow:0 0 0 2px rgba(78,115,223,.15);
}

.btn-transfer{
    background:#4e73df;
    color:white;
    border:none;
    padding:10px 18px;
    border-radius:10px;
    cursor:pointer;
}

.btn-transfer:hover{
    background:#2e59d9;
}

.transfer-preview{
    margin-top:10px;
    font-size:13px;
    color:#666;
}

.table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}

.table th, .table td{
    padding:10px;
    border-bottom:1px solid #eee;
}

.table th{
    background:#f8f9fc;
}

.pagination{
    display:flex;
    justify-content:center;
    gap:8px;
    margin-top:20px;
}

.page{
    min-width:34px;
    height:34px;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:0 10px;
    border-radius:8px;
    background:#f1f3f9;
    color:#333;
    text-decoration:none;
    font-size:0.85rem;
    transition:0.2s;
}

.page:hover{
    background:#4e73df;
    color:#fff;
    transform:translateY(-2px);
}

.page.active{
    background:#4e73df;
    color:#fff;
    font-weight:bold;
    box-shadow:0 4px 10px rgba(78,115,223,0.3);
}

.page.disabled{
    opacity:0.4;
    cursor:not-allowed;
}

.page.dots{
    background:transparent;
}
</style>

{{-- ================= FORM ================= --}}
<div class="card-box">
    <h4>➕ Transfer Baru</h4>

    <form id="formTransfer">
        @csrf

        <div class="form-grid">

            <select id="from_account" class="form-control" required>
                <option value="">Dari Rekening</option>
                @foreach($accounts as $a)
                    <option value="{{ $a->id }}">{{ $a->name }}</option>
                @endforeach
            </select>

            <select id="to_account" class="form-control" required>
                <option value="">Ke Rekening</option>
                @foreach($accounts as $a)
                    <option value="{{ $a->id }}">{{ $a->name }}</option>
                @endforeach
            </select>

            <input type="text" id="amount" class="form-control"
                   placeholder="Nominal">

            <input type="date" id="transfer_date"
                   class="form-control"
                   value="{{ date('Y-m-d') }}">

        </div>

        <div class="transfer-preview" id="previewText"></div>

        <div style="margin-top:15px; text-align:right;">
            <button type="submit" class="btn-transfer">
                🔁 Transfer Sekarang
            </button>
        </div>
    </form>
</div>

{{-- ================= FILTER ================= --}}
<div class="card-box">
    <form method="GET">
        <div class="form-grid">

            <input type="date" name="start_date" class="form-control"
                value="{{ request('start_date') }}">

            <input type="date" name="end_date" class="form-control"
                value="{{ request('end_date') }}">

            <button class="btn-transfer">🔍 Filter</button>

        </div>
    </form>
</div>

{{-- ================= TABLE ================= --}}
<div class="card-box">
    <div class="card-box" style="margin-bottom:15px;">
        <h4>📜 Riwayat Transfer</h4>
        <div class="card-box" style="margin-bottom:15px;">
            <div style="display:flex; flex-wrap:wrap; gap:15px;">

                {{-- TOTAL TRANSFER --}}
                <div style="flex:1; min-width:200px; background:#f8f9fc; padding:15px; border-radius:12px;">
                    <div style="font-size:0.8rem; color:#888;">Total Transfer</div>
                    <div style="font-size:1.3rem; font-weight:bold; color:#4e73df;">
                        Rp{{ number_format($totalTransfer,0,',','.') }}
                    </div>
                </div>

                {{-- PER ACCOUNT --}}
                @foreach($accounts as $acc)
                @php
                    $val = $accountTotals[$acc->id] ?? 0;
                @endphp

                <div style="flex:1; min-width:200px; background:#f8f9fc; padding:15px; border-radius:12px;">
                    
                    <div style="font-size:0.8rem; color:#888;">
                        {{ $acc->name }}
                    </div>

                    <div style="
                        font-size:1.2rem;
                        font-weight:bold;
                        color: {{ $val < 0 ? '#e74a3b' : '#1cc88a' }};
                    ">
                        {{ $val < 0 ? '-' : '+' }}
                        Rp{{ number_format(abs($val),0,',','.') }}
                    </div>

                    <div style="font-size:0.7rem; color:#aaa;">
                        Transfer saja
                    </div>

                </div>
                @endforeach

            </div>
        </div>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Dari</th>
                <th>Ke</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transfers as $t)
            <tr>
                <td>{{ \Carbon\Carbon::parse($t->transfer_date)->format('d M Y') }}</td>
                <td>{{ $t->fromAccount->name }}</td>
                <td>{{ $t->toAccount->name }}</td>
                <td><strong>Rp{{ number_format($t->amount,0,',','.') }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center;">Belum ada transfer</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if ($transfers->lastPage() > 1)
        <div class="pagination">

            {{-- PREV --}}
            @if ($transfers->onFirstPage())
                <span class="page disabled">‹</span>
            @else
                <a href="{{ $transfers->previousPageUrl() }}" class="page">‹</a>
            @endif

            {{-- NUMBER --}}
            @for ($i = 1; $i <= $transfers->lastPage(); $i++)
                @if ($i == $transfers->currentPage())
                    <span class="page active">{{ $i }}</span>
                @elseif ($i <= 2 || $i > $transfers->lastPage() - 2 || abs($i - $transfers->currentPage()) <= 1)
                    <a href="{{ $transfers->url($i) }}" class="page">{{ $i }}</a>
                @elseif ($i == 3 || $i == $transfers->lastPage() - 2)
                    <span class="page dots">...</span>
                @endif
            @endfor

            {{-- NEXT --}}
            @if ($transfers->hasMorePages())
                <a href="{{ $transfers->nextPageUrl() }}" class="page">›</a>
            @else
                <span class="page disabled">›</span>
            @endif

        </div>

        <div style="text-align:center; margin-top:8px; font-size:0.8rem; color:#888;">
            Menampilkan {{ $transfers->firstItem() }} - {{ $transfers->lastItem() }} 
            dari {{ $transfers->total() }} data
        </div>
        @endif

    {{ $transfers->links() }}
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let rawAmount = 0;

// FORMAT RUPIAH
document.getElementById('amount').addEventListener('input', function(e){
    let value = e.target.value.replace(/\D/g,'');
    rawAmount = value;

    e.target.value = new Intl.NumberFormat('id-ID').format(value);

    updatePreview();
});

// VALIDASI REKENING
document.getElementById('to_account').addEventListener('change', function(){
    let from = document.getElementById('from_account').value;
    let to = this.value;

    if(from && to && from === to){
        Swal.fire({
            icon:'warning',
            title:'Oops',
            text:'Rekening tidak boleh sama'
        });
        this.value = '';
    }

    updatePreview();
});

// PREVIEW
function updatePreview(){
    let from = document.getElementById('from_account');
    let to = document.getElementById('to_account');

    if(from.value && to.value && rawAmount){
        document.getElementById('previewText').innerText =
            `Transfer Rp${new Intl.NumberFormat('id-ID').format(rawAmount)} dari ${from.options[from.selectedIndex].text} ke ${to.options[to.selectedIndex].text}`;
    }
}

// SUBMIT AJAX
document.getElementById('formTransfer').addEventListener('submit', function(e){
    e.preventDefault();

    let from = document.getElementById('from_account').value;
    let to = document.getElementById('to_account').value;
    let date = document.getElementById('transfer_date').value;

    if(!from || !to || !rawAmount){
        Swal.fire('Error','Lengkapi data','error');
        return;
    }

    Swal.fire({
        title:'Memproses...',
        allowOutsideClick:false,
        didOpen:()=>Swal.showLoading()
    });

    fetch("{{ route('umkm.transaction.store_transfer') }}", {
        method:'POST',
        headers:{
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body:new URLSearchParams({
            from_account_id:from,
            to_account_id:to,
            amount:rawAmount,
            transfer_date:date
        })
    })
    .then(res=>res.json())
    .then(res=>{
        if(res.status === 'success'){
            Swal.fire('Berhasil','Transfer sukses','success')
                .then(()=>location.reload());
        }else{
            Swal.fire('Error',res.message,'error');
        }
    });
});
</script>

@endsection