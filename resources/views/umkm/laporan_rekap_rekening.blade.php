@extends('layouts.app')

@section('title', 'Laporan Rekap Rekening')

@section('content')

<style>
    .btn-send {
        padding: 13px 13px !important;
    }
</style>

<h1 class="title">📊 Laporan Rekap Rekening</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.report.rekap_rekening') }}" class="active">Laporan Rekap Rekening</a></li>
</ul>

<div class="data">
    <div class="content-data">
        <div class="head">
            <div>
                <h3>Laporan Rekap Rekening</h3>
                <p style="margin: 4px 0 0; color: #6b7280;">Menampilkan ringkasan saldo per rekening berdasarkan periode.</p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('umkm.report.rekap_rekening.export_excel', request()->query()) }}" class="btn-send">Export Excel</a>
                <a href="{{ route('umkm.report.rekap_rekening.export_pdf', request()->query()) }}" class="btn-send">Export PDF</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding: 20px;">
                <form method="GET" class="form-row" style="gap:12px; align-items:flex-end; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="start_date" style="display:flex; align-items:center; flex-direction:row-reverse">Tanggal awal</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $start }}" class="form-control">
                    </div>
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="end_date" style="display:flex; align-items:center; flex-direction:row-reverse">Tanggal akhir</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $end }}" class="form-control">
                    </div>
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="account_id" style="display:flex; align-items:center; flex-direction:row-reverse">Rekening</label>
                        <select id="account_id" name="account_id" class="form-control">
                            <option value="">Semua Rekening</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ (string) $accountId === (string) $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="per_page" style="display:flex; align-items:center; flex-direction:row-reverse">Show entries</label>
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
                    <table class="table table-striped table-hover" style="min-width:900px;">
                        <thead>
                            <tr>
                                <th style="min-width:120px;">Kode Rekening</th>
                                <th>Nama Rekening</th>
                                <th class="text-right">Saldo Awal</th>
                                <th class="text-right">Total Debit</th>
                                <th class="text-right">Total Kredit</th>
                                <th class="text-right">Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td class="text-right">Rp {{ number_format($item->opening, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($item->debit, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($item->credit, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($item->ending, 0, ',', '.') }}</td>
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
