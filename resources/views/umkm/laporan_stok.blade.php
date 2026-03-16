@extends('layouts.app')

@section('title', 'Laporan Stok')

@section('content')

<h1 class="title">📦 Laporan Stok</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.report.stok') }}" class="active">Laporan Stok</a></li>
</ul>

<div class="data">
    <div class="content-data">
        <div class="head">
            <div>
                <h3>Laporan Stok</h3>
                <p style="margin: 4px 0 0; color: #6b7280;">Menampilkan riwayat masuk/keluar stok untuk produk.</p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('umkm.report.stok.export_excel', request()->query()) }}" class="btn-send">Export Excel</a>
                <a href="{{ route('umkm.report.stok.export_pdf', request()->query()) }}" class="btn-send">Export PDF</a>
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
                        <label for="product_id" style="display:flex; align-items:center; flex-direction:row-reverse">
                            Produk
                        </label>
                        <select id="product_id" name="product_id" class="form-control">
                            <option value="">Semua Produk</option>
                            @foreach($products as $id => $name)
                                <option value="{{ $id }}" {{ (string) $productId === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
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

                    <div class="form-group" style="flex:1; min-width:220px; max-width:250px">
                        <button type="submit" class="btn-send" style="width:100%;">Filter</button>
                    </div>
                </form>

                <div class="table-container" style="margin-top:20px; overflow-x:auto;">
                    <table class="table table-striped table-hover" style="min-width:1100px;">
                        <thead>
                            <tr>
                                <th style="min-width:120px;">Tanggal</th>
                                <th>Produk</th>
                                <th>Variasi</th>
                                <th class="text-right">Stok Awal</th>
                                <th class="text-right">Stok Masuk</th>
                                <th class="text-right">Stok Keluar</th>
                                <th class="text-right">Saldo Akhir</th>
                                <th class="text-right">HPP</th>
                                <th class="text-right">Nilai Stok</th>
                                <th class="text-right">Potensi Laba</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>{{ optional($item->created_at) ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $item->product?->name ?? '-' }}</td>
                                    <td>{{ $item->variation?->name ?? '-' }}</td>
                                    <td class="text-right">{{ number_format($item->stok_awal ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($item->stok_masuk ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($item->stok_keluar ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($item->saldo ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($item->hpp ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($item->nilai_stok ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($item->potensi_laba ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center; padding: 20px;">Tidak ada data untuk periode ini.</td>
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
