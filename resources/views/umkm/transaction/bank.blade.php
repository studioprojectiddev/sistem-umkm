@extends('layouts.app')

@section('title', 'Bank / E-wallet')

@section('content')

<h1 class="title">💰 Bank / E-wallet</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li class="active">Bank / E-wallet</li>
</ul>

<div class="info-data">

    {{-- CARD UTAMA --}}
    <div class="card" style="padding:20px;">

        {{-- HEADER --}}
        <div style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
        ">
            <h4 style="margin:0;">💳 Daftar Rekening</h4>

            <button class="btn-main" onclick="openModal()">
                ➕ Tambah Rekening
            </button>
        </div>

        {{-- LIST REKENING --}}
        <div style="
            display:grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap:20px;
        ">

        @foreach($accounts as $acc)

            @php
                $used = $acc->cashflows->count() > 0;
            @endphp

            <div class="rekening-card-modern">

                {{-- HEADER --}}
                <div class="rekening-header">
                    <div class="rekening-title">
                        {{ $acc->name }}
                    </div>

                    <div class="rekening-type">
                        {{ strtoupper($acc->type ?? 'wallet') }}
                    </div>
                </div>

                {{-- STATUS --}}
                @if($used)
                    <div class="rekening-status used">
                        🔒 Digunakan di transaksi
                    </div>
                @else
                    <div class="rekening-status free">
                        ✅ Belum digunakan
                    </div>
                @endif

                {{-- ACTION --}}
                <div class="rekening-actions">

                    <button class="btn-icon edit"
                        onclick='editAccount({{ $acc->id }}, @json($acc->name), {{ $used ? "true" : "false" }})'>
                        ✏️
                    </button>

                    <button class="btn-icon delete"
                        onclick="deleteAccount({{ $acc->id }}, {{ $used ? 'true' : 'false' }})">
                        ❌
                    </button>

                </div>

            </div>

        @endforeach

        </div>

    </div>

</div>

{{-- ================= MODAL ================= --}}
<div id="modalAccount" class="modal-wrap">

    <div class="modal-box">
        <h4 id="modalTitle">Tambah Rekening</h4>
        <small id="lockInfo" style="color:#e74a3b; display:none;">
            ⚠️ Rekening sudah memiliki transaksi, hanya nama yang bisa diubah
        </small>

        <input type="hidden" id="account_id">

        <input type="text" id="account_name" class="form-control" placeholder="Nama rekening">

        <div class="modal-footer">
            <button onclick="closeModal()" class="btn-secondary">Batal</button>
            <button onclick="saveAccount()" class="btn-main">Simpan</button>
        </div>
    </div>

</div>

{{-- ================= STYLE ================= --}}
<style>

.btn-main{
    background:#4e73df;
    color:#fff;
    border:none;
    padding:8px 14px;
    border-radius:8px;
    cursor:pointer;
}

.btn-secondary{
    background:#ccc;
    border:none;
    padding:8px 14px;
    border-radius:8px;
}

.form-control{
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #ddd;
    margin-top:10px;
}

.rekening-card-modern{
    background:#fff;
    padding:18px;
    border-radius:16px;
    box-shadow:0 6px 20px rgba(0,0,0,0.06);
    transition:0.25s;
    position:relative;
}

.rekening-card-modern:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

/* HEADER */
.rekening-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:10px;
}

.rekening-title{
    font-weight:600;
    font-size:16px;
}

.rekening-type{
    font-size:11px;
    background:#eef2ff;
    color:#4e73df;
    padding:4px 8px;
    border-radius:8px;
}

/* STATUS */
.rekening-status{
    font-size:12px;
    margin-bottom:12px;
}

.rekening-status.used{
    color:#e74a3b;
}

.rekening-status.free{
    color:#1cc88a;
}

/* ACTION */
.rekening-actions{
    display:flex;
    gap:8px;
    justify-content:flex-end;
}

.btn-icon{
    border:none;
    padding:6px 10px;
    border-radius:8px;
    cursor:pointer;
    transition:0.2s;
}

.btn-icon.edit{
    background:#f6c23e;
}

.btn-icon.delete{
    background:#e74a3b;
    color:#fff;
}

.btn-icon:hover{
    transform:scale(1.1);
}

/* CARD REKENING */
.rekening-card{
    background:#f8f9fc;
    padding:15px;
    border-radius:12px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    transition:0.2s;
}

.rekening-card:hover{
    transform:translateY(-3px);
    box-shadow:0 6px 15px rgba(0,0,0,0.05);
}

.rekening-name{
    font-weight:600;
}

/* ACTION BUTTON */
.rekening-action{
    display:flex;
    gap:5px;
}

.btn-edit{
    background:#f6c23e;
    border:none;
    padding:5px 8px;
    border-radius:6px;
    cursor:pointer;
}

.btn-delete{
    background:#e74a3b;
    color:#fff;
    border:none;
    padding:5px 8px;
    border-radius:6px;
    cursor:pointer;
}

/* MODAL */
.modal-wrap{
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.4);
    justify-content:center;
    align-items:center;
}

.modal-box{
    background:#fff;
    padding:20px;
    border-radius:12px;
    width:300px;
}

.modal-footer{
    margin-top:15px;
    text-align:right;
}

</style>

<script>

// ================= MODAL =================
function openModal(){
    const modal = document.getElementById('modalAccount');
    modal.style.display = 'flex';
}

function resetModal(){
    document.getElementById('account_id').value = '';
    document.getElementById('account_name').value = '';
    document.getElementById('modalTitle').innerText = 'Tambah Rekening';
    document.getElementById('lockInfo').style.display = 'none';
}

function closeModal(){
    document.getElementById('modalAccount').style.display = 'none';
}

// ================= EDIT =================
function editAccount(id, name, used){
    console.log('EDIT:', id, name, used); // debug (boleh dihapus nanti)

    openModal(); // ❗ hanya buka, tidak reset

    document.getElementById('account_id').value = id;
    document.getElementById('account_name').value = name;
    document.getElementById('modalTitle').innerText = 'Edit Rekening';

    document.getElementById('lockInfo').style.display = used ? 'block' : 'none';

    // auto focus biar UX enak
    setTimeout(()=>{
        document.getElementById('account_name').focus();
    },100);
}

// ================= SAVE =================
function saveAccount(){

    let id = document.getElementById('account_id').value;
    let name = document.getElementById('account_name').value.trim();

    if(!name){
        Swal.fire('Error','Nama rekening wajib diisi','error');
        return;
    }

    let url = id 
        ? '/umkm/bank/update/' + id
        : '/umkm/bank/store';

    Swal.fire({
        title:'Menyimpan...',
        allowOutsideClick:false,
        didOpen:()=>Swal.showLoading()
    });

    // 🔥 gunakan FormData (lebih aman untuk Laravel)
    let formData = new FormData();
    formData.append('name', name);

    fetch(url, {
        method:'POST',
        headers:{
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: formData
    })
    .then(res => res.json())
    .then(res => {

        if(res.status === 'success'){
            Swal.fire('Berhasil', res.message, 'success')
                .then(()=>location.reload());
        }else{
            Swal.fire('Error', res.message || 'Gagal menyimpan','error');
        }

    })
    .catch(err=>{
        console.error(err);
        Swal.fire('Error','Terjadi kesalahan sistem','error');
    });
}

// ================= DELETE =================
function deleteAccount(id, used){

    if(used){
        Swal.fire(
            'Tidak bisa dihapus',
            'Rekening sudah digunakan di transaksi',
            'warning'
        );
        return;
    }

    Swal.fire({
        title: 'Yakin hapus?',
        text: "Data tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {

        if(result.isConfirmed){

            fetch('/umkm/bank/delete/' + id, {
                method:'DELETE',
                headers:{
                    'X-CSRF-TOKEN':'{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(res => {

                if(res.status === 'success'){
                    Swal.fire('Berhasil', res.message, 'success')
                        .then(()=>location.reload());
                }else{
                    Swal.fire('Error', res.message || 'Gagal hapus','error');
                }

            })
            .catch(err=>{
                console.error(err);
                Swal.fire('Error','Terjadi kesalahan','error');
            });

        }

    });
}

</script>

@endsection