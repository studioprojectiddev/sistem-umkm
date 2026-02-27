@extends('layouts.app')

@section('title', 'Pemasukan / Pengeluaran')

@section('content')

<h1 class="title">💰 Pemasukan / Pengeluaran</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.transaction.income') }}" class="active">Pemasukan / Pengeluaran</a></li>
</ul>

<style>
    /* ================= MODAL CUSTOM ================= */

.custom-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.custom-modal.active {
    display: flex;
}

.custom-modal-content {
    background: #fff;
    width: 500px;
    max-width: 95%;
    border-radius: 16px;
    padding: 20px;
    animation: modalFade .25s ease;
}

@keyframes modalFade {
    from { opacity:0; transform: translateY(-10px); }
    to { opacity:1; transform: translateY(0); }
}

.custom-modal-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
}

.custom-modal-header h3 {
    margin:0;
}

.close-modal {
    background:none;
    border:none;
    font-size:20px;
    cursor:pointer;
}

.custom-modal-body {
    display:flex;
    flex-direction:column;
    gap:12px;
}

.custom-modal-footer {
    margin-top:15px;
    display:flex;
    justify-content:flex-end;
    gap:10px;
}

.btn-secondary {
    background:#eee;
    border:none;
    padding:8px 14px;
    border-radius:8px;
    cursor:pointer;
}
.action-group{
    display:flex;
    gap:8px;
    align-items:center;
}

.btn-action{
    font-size:13px;
    padding:6px 12px;
    border-radius:20px;
    text-decoration:none;
    border:none;
    cursor:pointer;
    transition: all 0.25s ease;
    font-weight:500;
}

/* EDIT */
.btn-edit{
    background:#e8f0ff;
    color:#3b82f6;
}

.btn-edit:hover{
    background:#3b82f6;
    color:white;
    transform:translateY(-1px);
    box-shadow:0 4px 10px rgba(59,130,246,0.3);
}

/* DELETE */
.btn-delete{
    background:#ffe8e8;
    color:#ef4444;
}

.btn-delete:hover{
    background:#ef4444;
    color:white;
    transform:translateY(-1px);
    box-shadow:0 4px 10px rgba(239,68,68,0.3);
}

/* TRASH */
.btn-trash{
    background:#f3f4f6;
    color:#6b7280;
}

.btn-trash:hover{
    background:#6b7280;
    color:white;
    transform:translateY(-1px);
}

.transaction-card {
    padding:24px;
    border-radius:16px;
}

.section-title {
    margin-bottom:20px;
    font-weight:600;
}

.form-wrapper {
    display:flex;
    flex-direction:column;
    gap:18px;
}

.form-row {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(180px,1fr));
    gap:16px;
}

.form-group {
    display:flex;
    flex-direction:column;
    gap:6px;
}

.form-group label {
    font-size:0.85rem;
    color:#666;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding:10px 12px;
    border-radius:10px;
    border:1px solid #ddd;
    font-size:0.9rem;
    transition:all .2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color:#4e73df;
    outline:none;
    box-shadow:0 0 0 2px rgba(78,115,223,.15);
}

.form-group textarea {
    min-height:60px;
    resize:vertical;
}

.flex-2 {
    grid-column:span 2;
}

.form-actions {
    display:flex;
    justify-content:flex-end;
}

.btn-primary {
    background:#4e73df;
    color:white;
    padding:10px 18px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:500;
    transition:all .2s ease;
}

.btn-primary:hover {
    background:#2e59d9;
}

/* =========================
   CARD BASE
========================= */

.card-section{
    background:#fff;
    padding:22px;
    border-radius:18px;
    box-shadow:0 6px 20px rgba(0,0,0,0.05);
    margin-bottom:22px;
}

/* =========================
   SUMMARY
========================= */

.cash-summary{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:18px;
}

.summary-card{
    padding:20px;
    border-radius:16px;
    background:#fff;
    box-shadow:0 4px 15px rgba(0,0,0,0.05);
}

.summary-card small{
    color:#888;
}

.summary-card h2{
    margin-top:6px;
    font-size:1.7rem;
    font-weight:700;
}

.text-success{color:#1cc88a;}
.text-danger{color:#e74a3b;}

.closing-card {
    padding:20px;
    border-radius:16px;
    margin-bottom:20px;
}

.closing-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:15px;
    margin-bottom:15px;
}

.closing-form {
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.closing-form select {
    padding:8px 10px;
    border-radius:8px;
    border:1px solid #ddd;
    font-size:0.85rem;
}

.btn-lock {
    background:#f6c23e;
    color:#333;
    padding:8px 14px;
    border:none;
    border-radius:8px;
    font-weight:500;
    cursor:pointer;
    transition:all .2s ease;
}

.btn-lock:hover {
    background:#e0a800;
}

.closing-status {
    display:flex;
    flex-wrap:wrap;
    gap:10px;
}

.closing-badge {
    padding:6px 12px;
    background:#ffe8e8;
    color:#e74a3b;
    border-radius:20px;
    font-size:0.8rem;
    font-weight:500;
}

.closing-empty {
    color:#888;
    font-size:0.85rem;
}

/* =========================
   FORM GRID
========================= */

.form-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:14px;
}

.form-control{
    padding:10px 12px;
    border-radius:10px;
    border:1px solid #ddd;
    font-size:0.9rem;
}

textarea.form-control{
    resize:none;
}

.btn-primary{
    background:#4e73df;
    color:white;
    padding:10px 18px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    transition:0.2s ease;
}

.btn-primary:hover{
    background:#2e59d9;
}

/* =========================
   FILTER FLEX
========================= */

.filter-grid{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

/* =========================
   CHART
========================= */

.chart-wrapper{
    overflow-x:auto;
}

.chart-bar{
    display:flex;
    gap:14px;
    align-items:flex-end;
    min-width:650px;
    padding-top:20px;
}

.bar-group{
    display:flex;
    flex-direction:column;
    align-items:center;
}

.bar-income{
    width:18px;
    background:#1cc88a;
    border-radius:6px 6px 0 0;
    margin-bottom:3px;
}

.bar-expense{
    width:18px;
    background:#e74a3b;
    border-radius:6px 6px 0 0;
}

/* =========================
   TABLE
========================= */

.table-responsive{
    overflow-x:auto;
}

.analytics-table{
    width:100%;
    border-collapse:collapse;
    min-width:700px;
    margin-top:15px;
}

.analytics-table th,
.analytics-table td{
    padding:11px;
    border-bottom:1px solid #eee;
}

.analytics-table th{
    background:#f8f9fc;
    font-weight:600;
}

.analytics-table tbody tr:hover{
    background:#f4f6fb;
}

.badge-income{
    background:#e6f9f2;
    color:#1cc88a;
    padding:4px 10px;
    border-radius:6px;
    font-size:0.8rem;
}

.badge-expense{
    background:#fdecea;
    color:#e74a3b;
    padding:4px 10px;
    border-radius:6px;
    font-size:0.8rem;
}

/* =========================
   MOBILE
========================= */

@media(max-width:768px){
    .card-section{
        padding:16px;
    }
}

</style>

{{-- ================= SUMMARY ================= --}}

@php
$saldo = $totalIncome - $totalExpense;
@endphp

<div class="cash-summary" style="margin-top:20px;">

    <div class="summary-card">
        <small>Total Pemasukan</small>
        <h2 class="text-success">
            Rp{{ number_format($totalIncome ?? 0,0,',','.') }}
        </h2>
    </div>

    <div class="summary-card">
        <small>Total Pengeluaran</small>
        <h2 class="text-danger">
            Rp{{ number_format($totalExpense ?? 0,0,',','.') }}
        </h2>
    </div>

    <div class="summary-card">
        <small>Saldo Bersih</small>
        <h2 class="{{ $saldo>=0?'text-success':'text-danger' }}">
            Rp{{ number_format($saldo,0,',','.') }}
        </h2>
    </div>

    {{-- 🔥 Tambahan Comparison Card --}}

    <div class="summary-card">
        <small>Income Bulan Ini</small>
        <h2 class="text-success">
            Rp{{ number_format($incomeCurrent,0,',','.') }}
        </h2>
        <small>
            @if($incomeGrowth >= 0)
                📈 Naik {{ number_format($incomeGrowth,1) }}%
            @else
                📉 Turun {{ number_format(abs($incomeGrowth),1) }}%
            @endif
        </small>
    </div>

    <div class="summary-card">
        <small>Expense Bulan Ini</small>
        <h2 class="text-danger">
            Rp{{ number_format($expenseCurrent,0,',','.') }}
        </h2>
        <small>
            @if($expenseGrowth >= 0)
                ⚠️ Naik {{ number_format($expenseGrowth,1) }}%
            @else
                ✅ Turun {{ number_format(abs($expenseGrowth),1) }}%
            @endif
        </small>
    </div>

</div>

{{-- ================= FORM ================= --}}

<div class="card-section transaction-card" style="margin-top:20px">

    <h4 class="section-title">Tambah Transaksi</h4>

    <form method="POST" action="{{ route('umkm.transaction.store_income') }}">
        @csrf

        <div class="form-wrapper">

            <!-- Row 1 -->
            <div class="form-row">
                <div class="form-group">
                    <label>Tipe</label>
                    <select name="type" required>
                        <option value="">Pilih Tipe</option>
                        <option value="income">Pemasukan</option>
                        <option value="expense">Pengeluaran</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Kategori</label>
                    <select name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                data-type="{{ $cat->type }}">
                                {{ $cat->name }} ({{ ucfirst($cat->type) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Nominal</label>
                    <input type="number" name="amount"
                           placeholder="Masukkan nominal"
                           required>
                </div>

                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date"
                           name="transaction_date"
                           value="{{ date('Y-m-d') }}"
                           required>
                </div>
            </div>

            <!-- Row 2 -->
            <div class="form-row">
                <div class="form-group flex-2">
                    <label>Keterangan</label>
                    <textarea name="description"
                              placeholder="Catatan transaksi (opsional)">
                    </textarea>
                </div>

                <div class="form-group">
                    <label>Rekening</label>
                    <select name="account_id" required>
                        <option value="">Pilih Rekening</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">
                                {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Button -->
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    💾 Simpan Transaksi
                </button>
            </div>

        </div>

    </form>
</div>

{{-- ================= FILTER ================= --}}

<div class="card-section">
    <form method="GET">
        <div class="filter-grid">

            <select name="month" class="form-control">
                <option value="">Semua Bulan</option>
                @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}"
                        {{ request('month')==$m?'selected':'' }}>
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endfor
            </select>

            <select name="year" class="form-control">
                <option value="{{ now()->year }}">{{ now()->year }}</option>
            </select>

            <select name="type" class="form-control">
                <option value="">Semua Tipe</option>
                <option value="income"
                    {{ request('type')=='income'?'selected':'' }}>
                    Pemasukan
                </option>
                <option value="expense"
                    {{ request('type')=='expense'?'selected':'' }}>
                    Pengeluaran
                </option>
            </select>

            <button class="btn-primary">Filter</button>

        </div>
    </form>
</div>

<div class="card-section">
    <h4>💳 Saldo Per Rekening</h4>

    <div class="cash-summary">
        @foreach($accountBalances as $acc)
            <div class="summary-card">
                <small>{{ $acc->name }}</small>
                <h2 class="{{ $acc->balance >= 0 ? 'text-success':'text-danger' }}">
                    Rp{{ number_format($acc->balance,0,',','.') }}
                </h2>
            </div>
        @endforeach
    </div>
</div>

{{-- ================= CHART ================= --}}

<div class="card-section">
    <h4>📊 Grafik Cashflow Tahun {{ now()->year }}</h4>

    <div style="height:320px;">
        <canvas id="cashflowChart"></canvas>
    </div>
</div>

<div class="card-section">
    <h4>🥧 Distribusi Pengeluaran per Kategori</h4>

    @if($expenseByCategory->isEmpty())
        <p style="color:#888;">Belum ada data pengeluaran tahun ini.</p>
    @else
        <div style="height:320px;">
            <canvas id="expenseDonut"></canvas>
        </div>
    @endif
</div>

<div class="card-section closing-card">

    <div class="closing-header">
        <h4>🔒 Closing Bulan</h4>

        <form method="POST"
              action="{{ route('umkm.transaction.close_month') }}"
              class="closing-form">
            @csrf

            <select name="month" required>
                @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}">
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endfor
            </select>

            <select name="year" required>
                <option value="{{ now()->year }}">{{ now()->year }}</option>
            </select>

            <button type="submit"
                onclick="return confirm('Yakin ingin mengunci bulan ini?')"
                class="btn-lock">
                🔒 Lock
            </button>
        </form>
    </div>

    <div class="closing-status">

        @forelse($lockedMonths as $lock)
            <div class="closing-badge">
                🔒 {{ \Carbon\Carbon::create($lock->year,$lock->month)->format('F Y') }}
            </div>
        @empty
            <div class="closing-empty">
                Belum ada bulan yang dikunci
            </div>
        @endforelse

    </div>

</div>

{{-- ================= TABLE ================= --}}

<div class="card-section">
    <h4>Riwayat Transaksi</h4>
    <a href="{{ route('umkm.transaction.export_income',[
        'month'=>request('month'),
        'year'=>request('year')
    ]) }}"
    class="btn-primary"
    style="margin-bottom:15px; display:inline-block;">
        📁 Export Excel
    </a>

    <div class="table-responsive">
        <table class="analytics-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Kategori</th>
                    <th>Nominal</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($cashflows as $c)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($c->transaction_date)->format('d M Y') }}</td>
                    <td>
                        @if($c->type=='income')
                            <span class="badge-income">Pemasukan</span>
                        @else
                            <span class="badge-expense">Pengeluaran</span>
                        @endif
                    </td>
                    <td>{{ $c->category->name ?? '-' }}</td>
                    <td>
                        <strong class="{{ $c->type=='income' ? 'text-success' : 'text-danger' }}">
                            Rp{{ number_format($c->amount,0,',','.') }}
                        </strong>
                    </td>
                    <td>{{ $c->description }}</td>
                    <td>
                        <div class="action-group">

                            <a href="javascript:void(0)" 
                                class="btn-action btn-edit btn-edit-income"
                                data-id="{{ $c->id }}"
                                data-type="{{ $c->type }}"
                                data-category="{{ $c->category_id }}"
                                data-account="{{ $c->account_id }}"
                                data-amount="{{ $c->amount }}"
                                data-date="{{ $c->transaction_date }}"
                                data-description="{{ $c->description }}">
                                ✏ Edit
                            </a>

                            <form action="{{ route('umkm.transaction.delete_income',$c->id) }}" 
                                method="POST" 
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete">
                                    🗑 Hapus
                                </button>
                            </form>

                            <a href="{{ route('umkm.transaction.trash') }}" 
                            class="btn-action btn-trash">
                                ♻ Cek data Terhapus
                            </a>

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;">
                        Belum ada transaksi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">
        {{ $cashflows->links() }}
    </div>
</div>

<div id="editIncomeModal" class="custom-modal">
    <div class="custom-modal-content">

        <div class="custom-modal-header">
            <h3>Edit Transaksi</h3>
            <button type="button" class="close-modal">&times;</button>
        </div>

        <form id="editIncomeForm">
            @csrf
            @method('PUT')

            <input type="hidden" id="edit_id">

            <div class="custom-modal-body">

                <div class="form-group">
                    <label>Tipe</label>
                    <select id="edit_type" class="form-control">
                        <option value="income">Pemasukan</option>
                        <option value="expense">Pengeluaran</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Kategori</label>
                    <select id="edit_category" class="form-control">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Rekening</label>
                    <select id="edit_account" class="form-control">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Nominal</label>
                    <input type="number" id="edit_amount" class="form-control">
                </div>

                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" id="edit_date" class="form-control">
                </div>

                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea id="edit_description" class="form-control"></textarea>
                </div>

            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn-secondary close-modal">Batal</button>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>

        </form>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('cashflowChart').getContext('2d');

const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [
            @foreach($monthly as $m)
                "{{ \Carbon\Carbon::create()->month((int)$m->month)->format('M') }}",
            @endforeach
        ],
        datasets: [
            {
                label: 'Pemasukan',
                data: [
                    @foreach($monthly as $m)
                        {{ $m->income }},
                    @endforeach
                ],
                backgroundColor: '#1cc88a'
            },
            {
                label: 'Pengeluaran',
                data: [
                    @foreach($monthly as $m)
                        {{ $m->expense }},
                    @endforeach
                ],
                backgroundColor: '#e74a3b'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false
        },
        plugins: {
            legend: {
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': Rp' +
                            context.raw.toLocaleString('id-ID');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});

// ================== SUBMIT EDIT ==================

document.getElementById('editIncomeForm')
.addEventListener('submit', function(e){

    e.preventDefault();

    let id = document.getElementById('edit_id').value;

    let formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'PUT');
    formData.append('type', document.getElementById('edit_type').value);
    formData.append('category_id', document.getElementById('edit_category').value);
    formData.append('account_id', document.getElementById('edit_account').value);
    formData.append('amount', document.getElementById('edit_amount').value);
    formData.append('transaction_date', document.getElementById('edit_date').value);
    formData.append('description', document.getElementById('edit_description').value);

    fetch('/umkm/transaction/update_income/' + id, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {

        if(data.status === 'success'){
            location.reload();
        } else {
            alert('Gagal update');
        }

    })
    .catch(error => {
        console.error(error);
        alert('Terjadi kesalahan sistem');
    });

});

const modal = document.getElementById('editIncomeModal');

document.addEventListener('click', function(e){

    // OPEN
    if(e.target.closest('.btn-edit-income')){

        let btn = e.target.closest('.btn-edit-income');

        document.getElementById('edit_id').value = btn.dataset.id;
        document.getElementById('edit_type').value = btn.dataset.type;
        document.getElementById('edit_category').value = btn.dataset.category;
        document.getElementById('edit_account').value = btn.dataset.account;
        document.getElementById('edit_amount').value = btn.dataset.amount;
        document.getElementById('edit_date').value = btn.dataset.date;
        document.getElementById('edit_description').value = btn.dataset.description;

        modal.classList.add('active');
    }

    // CLOSE
    if(e.target.classList.contains('close-modal') || e.target === modal){
        modal.classList.remove('active');
    }
});
</script>

@if(!$expenseByCategory->isEmpty())
<script>
const donutCtx = document.getElementById('expenseDonut').getContext('2d');

new Chart(donutCtx, {
    type: 'doughnut',
    data: {
        labels: [
            @foreach($expenseByCategory as $e)
                "{{ $e->category }}",
            @endforeach
        ],
        datasets: [{
            data: [
                @foreach($expenseByCategory as $e)
                    {{ $e->total }},
                @endforeach
            ],
            backgroundColor: [
                '#e74a3b',
                '#f6c23e',
                '#36b9cc',
                '#858796',
                '#5a5c69',
                '#4e73df',
                '#1cc88a'
            ]
        }]
    },
    options: {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{
                position:'right'
            },
            tooltip:{
                callbacks:{
                    label:function(context){
                        return context.label + ': Rp' +
                            context.raw.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});
</script>
@endif

@endsection