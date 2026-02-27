@extends('layouts.app')

@section('title','Data Terhapus')

@section('content')

<style>

.trash-card{
    background:#fff;
    padding:24px;
    border-radius:18px;
    box-shadow:0 8px 25px rgba(0,0,0,0.05);
}

.trash-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.trash-title{
    font-size:20px;
    font-weight:600;
}

.trash-table{
    width:100%;
    border-collapse:collapse;
}

.trash-table th{
    background:#f8f9fc;
    padding:14px;
    text-align:left;
    font-weight:600;
    font-size:14px;
}

.trash-table td{
    padding:14px;
    border-bottom:1px solid #f1f1f1;
    font-size:14px;
}

.trash-table tbody tr{
    transition:all .2s ease;
}

.trash-table tbody tr:hover{
    background:#f9fbff;
    transform:translateX(3px);
}

.deleted-date{
    font-size:12px;
    color:#888;
}

.deleted-badge{
    background:#fdeaea;
    color:#e74a3b;
    padding:4px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:500;
}

.action-group{
    display:flex;
    gap:8px;
}

.btn-restore{
    background:#e6f9f2;
    color:#1cc88a;
    padding:6px 12px;
    border:none;
    border-radius:20px;
    font-size:13px;
    cursor:pointer;
    transition:.2s ease;
}

.btn-restore:hover{
    background:#1cc88a;
    color:#fff;
    transform:translateY(-1px);
}

.btn-delete-permanent{
    background:#ffe8e8;
    color:#e74a3b;
    padding:6px 12px;
    border:none;
    border-radius:20px;
    font-size:13px;
    cursor:pointer;
    transition:.2s ease;
}

.btn-delete-permanent:hover{
    background:#e74a3b;
    color:#fff;
    transform:translateY(-1px);
}

.empty-state{
    text-align:center;
    padding:40px;
    color:#999;
}

</style>

<h2>🗑 Data Transaksi Terhapus</h2>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/ Pemasukan / Pengeluaran / </li>
    <li><a href="{{ route('umkm.transaction.trash') }}" class="active">Data Terhapus</a></li>
</ul>

<div class="trash-card" style="margin-top:20px;">

    <div class="trash-header">
        <a href="{{ route('umkm.transaction.income') }}" class="btn-restore">
            ← Kembali
        </a>
    </div>

    @if($trashed->isEmpty())

        <div class="empty-state">
            Tidak ada data yang dihapus
        </div>

    @else

    <table class="trash-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Nominal</th>
                <th>Dihapus Pada</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>

        @foreach($trashed as $t)
        <tr>
            <td>
                {{ \Carbon\Carbon::parse($t->transaction_date)->format('d M Y') }}
            </td>

            <td>
                {{ $t->category->name ?? '-' }}
            </td>

            <td>
                <strong>
                    Rp{{ number_format($t->amount,0,',','.') }}
                </strong>
            </td>

            <td>
                <span class="deleted-badge">
                    {{ \Carbon\Carbon::parse($t->deleted_at)->format('d M Y H:i') }}
                </span>
            </td>

            <td>
                <div class="action-group">

                    <form action="{{ route('umkm.transaction.restore',$t->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-restore">
                            ♻ Restore
                        </button>
                    </form>

                    <form action="{{ route('umkm.transaction.forceDelete',$t->id) }}"
                          method="POST"
                          onsubmit="return confirm('Hapus permanen? Data tidak bisa dikembalikan!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete-permanent">
                            ❌ Hapus Permanen
                        </button>
                    </form>

                </div>
            </td>
        </tr>
        @endforeach

        </tbody>
    </table>

    <div style="margin-top:20px;">
        {{ $trashed->links() }}
    </div>

    @endif

</div>

@endsection