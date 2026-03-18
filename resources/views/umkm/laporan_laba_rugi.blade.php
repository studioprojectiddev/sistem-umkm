@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')

@section('content')

<h1 class="title">📊 Laporan Laba Rugi</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.report.laba_rugi') }}" class="active">Laporan Laba Rugi</a></li>
</ul>

<div class="data">
    <div class="content-data">
        <div class="head">
            <div>
                <h3>Laporan Laba Rugi</h3>
                <p style="margin: 4px 0 0; color: #6b7280;">Laporan berdasarkan jurnal akuntansi (posted/posting).</p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('umkm.report.laba_rugi.export_excel', request()->query()) }}" class="btn-send">Export Excel</a>
                <a href="{{ route('umkm.report.laba_rugi.export_pdf', request()->query()) }}" class="btn-send">Export PDF</a>
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
                        <label for="per_page" style="display:flex; align-items:center; flex-direction:row-reverse">Show entries</label>
                        <select id="per_page" name="per_page" class="form-control">
                            @foreach(['10','25','50','100','all'] as $option)
                                <option value="{{ $option }}" {{ (string) $perPage === $option ? 'selected' : '' }}>{{ $option === 'all' ? 'All' : $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:220px; display:flex;  max-width:250px">
                        <button type="submit" class="btn-send" style="width:100%;">Tampilkan</button>
                    </div>
                </form>

                <div class="table-container" style="margin-top:20px; overflow-x:auto;">
                    <table class="table table-striped table-hover" style="min-width:700px;">
                        <thead>
                            <tr>
                                <th>Komponen</th>
                                <th class="text-right">Nilai (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td class="text-right">Rp {{ number_format($item['value'],0,',','.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-top:14px;">
                    <div>Periode: {{ $start ?: '-' }} s/d {{ $end ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .btn-send {
        padding: 9px 13px !important;
    }
</style>
@endpush
