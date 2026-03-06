@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')

<style>
    .btn-send {
        padding: 13px 13px !important;
    }
</style>

<h1 class="title">📈 Laporan Penjualan</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.report.sales') }}" class="active">Laporan Penjualan</a></li>
</ul>

<div class="data">
    <div class="content-data">
        <div class="head">
            <div>
                <h3>Laporan Penjualan</h3>
                <p style="margin: 4px 0 0; color: #6b7280;">Filter berdasarkan rentang tanggal, outlet, dan status.</p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('umkm.report.sales.export_excel', request()->query()) }}" class="btn-send">Export Excel</a>
                <a href="{{ route('umkm.report.sales.export_pdf', request()->query()) }}" class="btn-send">Export PDF</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding: 20px;">
                <form method="GET" class="form-row" style="gap:12px; align-items:flex-end; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="start_date">Tanggal awal</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $start }}" class="form-control">
                    </div>
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="end_date">Tanggal akhir</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $end }}" class="form-control">
                    </div>
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="warehouse_id" style="display:flex; align-items:center; flex-direction:row-reverse">
                            Outlet
                        </label>
                        <!-- <label for="warehouse_id">Outlet</label> -->
                        <select id="warehouse_id" name="warehouse_id" class="form-control">
                            <option value="">Semua Outlet</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="status">Status Transaksi</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">Semua Status</option>
                            @foreach(['pending','completed','cancelled','refunded'] as $st)
                                <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="per_page">Show entries</label>
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

                <div class="table-container" style="margin-top:20px;">
                    <table class="table table-striped table-hover" style="min-width:900px;">
                        <thead>
                            <tr>
                                <th style="min-width:120px;">Tanggal</th>
                                <th>Kode Transaksi</th>
                                <th>Pelanggan</th>
                                <th>Produk</th>
                                <th class="text-right">Jumlah</th>
                                <th class="text-right">Harga</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>{{ optional($item->transaction)->transaction_date ? \Carbon\Carbon::parse($item->transaction->transaction_date)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ optional($item->transaction)->invoice_number ?? '-' }}</td>
                                    <td>{{ optional($item->transaction)->customer_name ?: 'Umum' }}</td>
                                    <td>
                                        {{ optional($item->product)->name ?? '-' }}
                                        @if(optional($item->variation)->id)
                                            <br><small style="color:#6b7280;">({{ $item->variation->name ?? 'Varian' }})</small>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ $item->quantity }}</td>
                                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center; padding: 20px;">Tidak ada data untuk periode ini.</td>
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
                        Total penjualan: <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
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
