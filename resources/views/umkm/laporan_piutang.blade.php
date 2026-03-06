@extends('layouts.app')

@section('title', 'Laporan Piutang Penjualan')

@section('content')

<h1 class="title">📄 Laporan Piutang Penjualan</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.report.piutang') }}" class="active">Laporan Piutang Penjualan</a></li>
</ul>

<div class="data">
    <div class="content-data">
        <div class="head">
            <div>
                <h3>Laporan Piutang Penjualan</h3>
                <p style="margin: 4px 0 0; color: #6b7280;">Menampilkan transaksi penjualan yang belum lunas atau memiliki piutang.</p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('umkm.report.piutang.export_excel', request()->query()) }}" class="btn-send">Export Excel</a>
                <a href="{{ route('umkm.report.piutang.export_pdf', request()->query()) }}" class="btn-send">Export PDF</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding: 20px;">
                <form method="GET" class="form-row" style="gap:12px; align-items:flex-end; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="start_date" style="display:flex; align-items:center; flex-direction:row-reverse">
                            Tanggal awal
                        </label>
                        <input type="date" id="start_date" name="start_date" value="{{ $start }}" class="form-control">
                    </div>

                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="end_date" style="display:flex; align-items:center; flex-direction:row-reverse">
                            Tanggal akhir
                        </label>
                        <input type="date" id="end_date" name="end_date" value="{{ $end }}" class="form-control">
                    </div>

                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="customer_name" style="display:flex; align-items:center; flex-direction:row-reverse">
                            Nama Pelanggan
                        </label>
                        <select id="customer_name" name="customer_name" class="form-control">
                            <option value="">Semua Pelanggan</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer }}" {{ $customerName === $customer ? 'selected' : '' }}>{{ $customer }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="tempo" style="display:flex; align-items:center; flex-direction:row-reverse">
                            Tempo
                        </label>
                        <select id="tempo" name="tempo" class="form-control">
                            <option value="">Semua Tempo</option>
                            <option value="net_15" {{ $tempo === 'net_15' ? 'selected' : '' }}>Net 15</option>
                            <option value="net_30" {{ $tempo === 'net_30' ? 'selected' : '' }}>Net 30</option>
                            <option value="net_60" {{ $tempo === 'net_60' ? 'selected' : '' }}>Net 60</option>
                            <option value="net_90" {{ $tempo === 'net_90' ? 'selected' : '' }}>Net 90</option>
                            <option value="sdt" {{ $tempo === 'sdt' ? 'selected' : '' }}>Sudah Jatuh Tempo</option>
                        </select>
                    </div>

                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="status" style="display:flex; align-items:center; flex-direction:row-reverse">
                            Status Piutang
                        </label>
                        <select id="status" name="status" class="form-control">
                            <option value="belum" {{ $status === 'belum' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="lunas" {{ $status === 'lunas' ? 'selected' : '' }}>Lunas</option>
                        </select>
                    </div>

                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="q" style="display:flex; align-items:center; flex-direction:row-reverse">
                            Cari
                        </label>
                        <input type="text" id="q" name="q" value="{{ $search }}" class="form-control" placeholder="Cari nomor / pelanggan">
                    </div>

                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="per_page" style="display:flex; align-items:center; flex-direction:row-reverse">
                            Show entries
                        </label>
                        <select id="per_page" name="per_page" class="form-control">
                            @foreach(['10','25','50','100','all'] as $option)
                                <option value="{{ $option }}" {{ (string) $perPage === $option ? 'selected' : '' }}>{{ $option === 'all' ? 'All' : $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="flex:1; min-width:220px; display:flex; gap:8px; max-width:250px">
                        <button type="submit" class="btn-send" style="flex:1;">Tampilkan</button>
                    </div>
                </form>

                <div class="table-container" style="margin-top:20px; overflow-x:auto;">
                    <table class="table table-striped table-hover" style="min-width:1000px;">
                        <thead>
                            <tr>
                                <th style="min-width:120px;">Tanggal</th>
                                <th>No Transaksi</th>
                                <th>Pelanggan</th>
                                <th>Tempo</th>
                                <th class="text-right">Total Penjualan</th>
                                <th class="text-right">Sudah Dibayar</th>
                                <th class="text-right">Sisa Piutang</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                @php
                                    $paid = $item->uang_diterima ?? 0;
                                    $remaining = max(0, $item->total - $paid);
                                    $statusLabel = $remaining > 0 ? 'Belum Lunas' : 'Lunas';
                                @endphp
                                <tr>
                                    <td>{{ optional($item->transaction_date) ? \Carbon\Carbon::parse($item->transaction_date)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $item->invoice_number }}</td>
                                    <td>{{ $item->customer_name ?: 'Umum' }}</td>
                                    <td>{{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('d/m/Y') : '-' }}</td>
                                    <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($paid, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                                    <td>{{ $statusLabel }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="text-align:center; padding: 20px;">Tidak ada data untuk periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @php
                    $first = $items->firstItem() ?: 0;
                    $last = $items->lastItem() ?: 0;
                    $totalEntries = $items->total() ?? $items->count();
                @endphp

                <div class="" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-top:18px;">
                    <div>
                        Showing {{ $first }} to {{ $last }} of {{ $totalEntries }} entries.
                        Total piutang: <strong>Rp {{ number_format($totalPiutang, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .btn-send {
        padding: 13px 13px !important;
    }
</style>
@endpush

