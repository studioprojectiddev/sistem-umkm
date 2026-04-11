@extends('layouts.app')

@section('title', 'Laporan Arus Kas')

@section('content')

<h1 class="title">📄 Laporan Arus Kas</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.report.cashflow') }}" class="active">Laporan Arus Kas</a></li>
</ul>

<div class="data">
    <div class="content-data">
        <div class="head">
            <div>
                <h3>Laporan Arus Kas</h3>
                <p style="margin: 4px 0 0; color: #6b7280;">Berdasarkan cash flows dan rekening kas/bank/ewallet.</p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('umkm.report.cashflow.export_excel', request()->query()) }}" class="btn-send">Export Excel</a>
                <a href="{{ route('umkm.report.cashflow.export_pdf', request()->query()) }}" class="btn-send">Export PDF</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding: 20px;">
                <form method="GET" class="form-row" style="gap:12px; align-items:flex-end; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="start_date" style="display:flex; align-items:center; flex-direction:row-reverse">Tanggal awal</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $report['start'] }}" class="form-control">
                    </div>
                    <div class="form-group" style="flex:1; min-width:220px;">
                        <label for="end_date" style="display:flex; align-items:center; flex-direction:row-reverse">Tanggal akhir</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $report['end'] }}" class="form-control">
                    </div>
                    <div class="form-group" style="flex:1; min-width:220px; display:flex;  max-width:250px">
                        <button type="submit" class="btn-send" style="width:100%;">Tampilkan</button>
                    </div>
                </form>

                <div style="margin-top:20px;">
                    @foreach($report['categories'] as $category)
                        <div class="card" style="margin-bottom:20px;">
                            <div class="card-body" style="padding:18px;">
                                <h4 style="margin-bottom:12px; color:#1d4ed8;">{{ $category['label'] }}</h4>
                                <div class="table-container" style="overflow-x:auto;">
                                    <table class="table table-striped table-hover" style="min-width:760px;">
                                        <thead>
                                            <tr>
                                                <th>Deskripsi</th>
                                                <th class="text-right">Kas Masuk (IDR)</th>
                                                <th class="text-right">Kas Keluar (IDR)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($category['items'] as $item)
                                                <tr>
                                                    <td>{{ $item->description }}</td>
                                                    <td class="text-right">{{ $item->income ? 'Rp ' . number_format($item->income, 0, ',', '.') : '—' }}</td>
                                                    <td class="text-right">{{ $item->expense ? 'Rp ' . number_format($item->expense, 0, ',', '.') : '—' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td>Tidak ada data</td>
                                                    <td class="text-right">Rp 0</td>
                                                    <td class="text-right">Rp 0</td>
                                                </tr>
                                            @endforelse
                                            <tr style="background:#f3f4f6; font-weight:600;">
                                                <td>Total {{ $category['label'] }}</td>
                                                <td class="text-right">Rp {{ number_format($category['income'], 0, ',', '.') }}</td>
                                                <td class="text-right">Rp {{ number_format($category['expense'], 0, ',', '.') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="card" style="margin-bottom:0;">
                        <div class="card-body" style="padding:18px;">
                            <h4 style="margin-bottom:12px; color:#1d4ed8;">Ringkasan</h4>
                            <div class="table-container" style="overflow-x:auto;">
                                <table class="table table-striped" style="min-width:560px;">
                                    <tbody>
                                        <tr>
                                            <td>Total Kas Masuk</td>
                                            <td class="text-right">Rp {{ number_format($report['total_income'], 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Kas Keluar</td>
                                            <td class="text-right">Rp {{ number_format($report['total_expense'], 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Kenaikan / Penurunan Kas</td>
                                            <td class="text-right" style="color: {{ $report['net_change'] >= 0 ? '#047857' : '#b91c1c' }};">
                                                {{ $report['net_change'] >= 0 ? '' : '(' }}Rp {{ number_format(abs($report['net_change']), 0, ',', '.') }}{{ $report['net_change'] < 0 ? ')' : '' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Saldo Kas Awal</td>
                                            <td class="text-right">Rp {{ number_format($report['opening_balance'], 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Saldo Kas Akhir</td>
                                            <td class="text-right">Rp {{ number_format($report['ending_balance'], 0, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .btn-send { padding: 9px 13px !important; }
    .table td, .table th { white-space: nowrap; }
    .table-container { min-width: 100%; }
</style>
@endpush
