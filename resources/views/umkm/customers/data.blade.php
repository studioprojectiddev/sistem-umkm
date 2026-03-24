@extends('layouts.app')

@section('title', 'Data Pelanggan')

@section('content')
<h1 class="title">Data Pelanggan</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.customer.data') }}" class="active">Data Pelanggan</a></li>
</ul>

<style>
    .btn-bayar {
    background: #6c63ff;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    transition: 0.2s;
}

.btn-bayar:hover {
    background: #574fd6;
}

.pagination-wrapper {
    margin-top: 15px;
}

.pagination {
    display: flex;
    gap: 6px;
    list-style: none;
    padding: 0;
}

.pagination li a {
    display: block;
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid #ddd;
    text-decoration: none;
    font-size: 13px;
    color: #333;
    transition: all 0.2s ease;
}

/* hover */
.pagination li a:hover {
    background: #f3f4f6;
}

/* active */
.pagination li.active a {
    background: #6c63ff;
    color: white;
    border-color: #6c63ff;
}

/* disabled */
.pagination li.disabled a {
    color: #aaa;
    pointer-events: none;
    background: #f9f9f9;
}

/* info text */
.pagination-info {
    margin-top: 8px;
    font-size: 12px;
    color: #666;
}
</style>

<div class="info-data">
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3 style="margin:0;">Daftar Pelanggan</h3>
            <input type="text" id="searchInput" placeholder="Cari pelanggan..." 
                   style="padding:6px 10px; border-radius:6px; border:1px solid #ccc;">
        </div>

        <div style="overflow-x:auto;">
            <table class="table" id="customerTable" style="width:100%; border-collapse: collapse;">
                <thead style="background:#f5f5f5;">
                    <tr>
                        <th>Nama</th>
                        <th>Invoice</th>
                        <th>Kontak</th>
                        <th>Total Transaksi</th>
                        <th>Total</th>
                        <th>Sudah Bayar</th>
                        <th>Sisa Utang</th>
                        <th>Status</th>
                        <th>Metode</th>
                        <th>Jatuh Tempo</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $c)
                    <tr style="border-bottom:1px solid #eee; {{ $c->is_overdue ? 'background:#fff5f5;' : '' }}">
                        
                        <!-- Nama -->
                        <td>{{ $c->customer_name }}</td>

                        <!-- Invoice -->
                        <td>
                            <small style="color:#555;">
                                {{ $c->last_invoice ?? '-' }}
                            </small>
                        </td>

                        <!-- Kontak -->
                        <td>
                            @if($c->customer_phone)
                                @php
                                    $phone = preg_replace('/^0/', '62', $c->customer_phone);

                                    $pesan = "Halo Kak {$c->customer_name},%0A"
                                        . "Kami dari Immanuel Store ingin mengingatkan bahwa Anda memiliki utang.%0A%0A"
                                        . "Sisa utang: Rp " . number_format($c->total_unpaid, 0, ',', '.') . "%0A"
                                        . "Jatuh tempo: " . ($c->last_due_date ? date('d-m-Y', strtotime($c->last_due_date)) : '-') . "%0A%0A"
                                        . "Mohon segera dilakukan pembayaran ya 🙏%0A"
                                        . "Terima kasih.";
                                @endphp

                                <a href="https://wa.me/{{ $phone }}?text={{ $pesan }}" 
                                target="_blank"
                                style="color:#25D366; font-weight:500;">
                                    {{ $c->customer_phone }}
                                </a>
                            @else
                                -
                            @endif
                        </td>

                        <!-- Total Transaksi -->
                        <td>{{ $c->total_transaksi }}</td>

                        <!-- Total Belanja -->
                        <td>Rp {{ number_format($c->total_transaksi_amount) }}</td>

                        <!-- Sudah Bayar -->
                        <td style="color:green;">
                            Rp {{ number_format($c->total_bayar) }}
                        </td>

                        <!-- Sisa Utang -->
                        <td>
                            <strong style="color:{{ $c->total_unpaid > 0 ? 'red' : 'green' }}">
                                Rp {{ number_format($c->total_unpaid) }}
                            </strong>
                        </td>

                        <!-- Status -->
                        <td>
                            @if($c->status == 'Lunas')
                                <span style="background:#d4edda; color:#155724; padding:4px 8px; border-radius:5px;">
                                    Lunas
                                </span>
                            @else
                                <span style="background:#f8d7da; color:#721c24; padding:4px 8px; border-radius:5px;">
                                    Belum Lunas
                                </span>

                                @if($c->is_overdue)
                                    <div style="color:red; font-size:12px; margin-top:3px;">
                                        ⚠ Terlambat
                                    </div>
                                @endif
                            @endif
                        </td>

                        <!-- Metode -->
                        <td>{{ $c->metode_pembayaran ?? '-' }}</td>

                        <!-- Jatuh Tempo -->
                        <td>
                            @if($c->last_due_date)
                                <span style="color:{{ $c->is_overdue ? 'red' : '#333' }}">
                                    {{ date('d-m-Y', strtotime($c->last_due_date)) }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($c->total_unpaid > 0)
                                <button class="btn-bayar"
                                    data-nama="{{ $c->customer_name }}"
                                    data-sisa="{{ $c->total_unpaid }}">
                                    Bayar
                                </button>
                            @else
                                -
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center; padding:20px;">
                            Tidak ada data pelanggan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="pagination-wrapper">

                @if ($customers->hasPages())
                    <ul class="pagination">

                        {{-- PREV --}}
                        <li class="{{ $customers->onFirstPage() ? 'disabled' : '' }}">
                            <a href="{{ $customers->previousPageUrl() ?? '#' }}">«</a>
                        </li>

                        {{-- NUMBER (MAX 5 ANGKA) --}}
                        @php
                            $start = max(1, $customers->currentPage() - 2);
                            $end = min($customers->lastPage(), $customers->currentPage() + 2);
                        @endphp

                        @for ($i = $start; $i <= $end; $i++)
                            <li class="{{ $customers->currentPage() == $i ? 'active' : '' }}">
                                <a href="{{ $customers->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        {{-- NEXT --}}
                        <li class="{{ $customers->hasMorePages() ? '' : 'disabled' }}">
                            <a href="{{ $customers->nextPageUrl() ?? '#' }}">»</a>
                        </li>

                    </ul>

                    {{-- INFO --}}
                    <div class="pagination-info">
                        Menampilkan {{ $customers->firstItem() }} - {{ $customers->lastItem() }} 
                        dari {{ $customers->total() }} data
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

{{-- 🔍 SIMPLE SEARCH --}}
<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll('#customerTable tbody tr');

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(value) ? '' : 'none';
    });
});


// ===============================
// 🔥 BUTTON BAYAR
// ===============================
$(document).on('click', '.btn-bayar', function () {

    let nama = $(this).data('nama');
    let sisa = parseInt($(this).data('sisa'));

    Swal.fire({
        title: 'Pembayaran Utang',
        html: `
            <div style="text-align:left; font-size:14px;">

                <!-- HEADER -->
                <div style="margin-bottom:12px;">
                    <div style="font-size:16px; font-weight:600;">
                        ${nama}
                    </div>
                    <div style="color:#dc3545; font-size:14px; margin-top:4px;">
                        Sisa utang: 
                        <strong>Rp ${Number(sisa).toLocaleString()}</strong>
                    </div>
                </div>

                <!-- INPUT -->
                <div style="display:flex; gap:10px; align-items:end;">
                    
                    <div style="flex:1;">
                        <label style="font-size:12px; color:#666;">Jumlah Bayar</label>
                        <input id="bayar" type="text" class="swal2-input"
                            placeholder="Masukkan nominal"
                            style="margin:0; font-size:15px; font-weight:500;">
                    </div>

                    <div style="width:150px;">
                        <label style="font-size:12px; color:#666;">Metode</label>
                        <select id="account_id" class="swal2-input" style="margin:0;">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <!-- INFO SISA -->
                <div id="previewSisa" style="
                    margin-top:10px;
                    font-size:13px;
                    color:#666;
                ">
                    Sisa setelah bayar: -
                </div>

            </div>
            `,
        showCancelButton: true,
        confirmButtonText: 'Bayar',
        cancelButtonText: 'Batal',
        focusConfirm: false,

        didOpen: () => {
            let input = document.getElementById('bayar');
            let preview = document.getElementById('previewSisa');

            input.focus();

            input.addEventListener('input', function () {
                let val = parseInt(this.value.replace(/[^0-9]/g,'')) || 0;

                this.value = val.toLocaleString();

                let sisaBaru = sisa - val;

                if (sisaBaru < 0) {
                    preview.innerHTML = `<span style="color:red;">Melebihi utang</span>`;
                } else {
                    preview.innerHTML = `
                        Sisa setelah bayar: 
                        <strong style="color:${sisaBaru === 0 ? 'green' : '#333'}">
                            Rp ${sisaBaru.toLocaleString()}
                        </strong>
                    `;
                }
            });
        },

        preConfirm: () => {
            const bayar = parseInt($('#bayar').val() || 0);
            const account_id = $('#account_id').val();

            // validasi
            if (bayar <= 0) {
                Swal.showValidationMessage('Masukkan nominal pembayaran!');
                return false;
            }

            if (bayar > sisa) {
                Swal.showValidationMessage('Pembayaran melebihi sisa utang!');
                return false;
            }

            if (!account_id) {
                Swal.showValidationMessage('Pilih metode pembayaran!');
                return false;
            }

            return { bayar, account_id };
        }
    }).then(res => {

        if (!res.isConfirmed) return;

        // ===============================
        // 🔥 LOADING
        // ===============================
        Swal.fire({
            title: 'Memproses...',
            text: 'Mohon tunggu',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // ===============================
        // 🔥 AJAX POST
        // ===============================
        $.post('/umkm/customer/bayar', {
            _token: "{{ csrf_token() }}",
            customer_name: nama,
            jumlah: res.value.bayar,
            account_id: res.value.account_id
        })
        .done(function (response) {

            if (response.status === 'success') {

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Pembayaran berhasil disimpan'
                }).then(() => {
                    location.reload();
                });

            } else {

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Terjadi kesalahan'
                });

            }

        })
        .fail(function () {

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal menghubungi server'
            });

        });

    });

});
</script>

@endsection