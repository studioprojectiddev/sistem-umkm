@extends('layouts.app')

@section('title', 'Laporan Neraca')

@section('content')

<h1 class="title">📊 Laporan Neraca</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.report.neraca') }}" class="active">Laporan Neraca</a></li>
</ul>

<div class="data">
    <div class="content-data">
        <div class="head">
            <div>
                <h3>Laporan Neraca</h3>
                <p style="margin: 4px 0 0; color: #6b7280;">Laporan berdasarkan jurnal akuntansi (posted/posting).</p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('umkm.report.neraca.export_excel', request()->query()) }}" class="btn-send">Export Excel</a>
                <a href="{{ route('umkm.report.neraca.export_pdf', request()->query()) }}" class="btn-send">Export PDF</a>
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
                    <div class="form-group" style="flex:1; min-width:220px; display:flex;  max-width:250px">
                        <button type="submit" class="btn-send" style="width:100%;">Tampilkan</button>
                    </div>
                </form>

                <div class="table-container" style="margin-top:20px; overflow-x:auto;">
                    <table class="table table-striped table-hover" style="min-width:800px;">
                        <thead>
                            <tr>
                                <th>Nama Akun</th>
                                <th class="text-right">Debet</th>
                                <th class="text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($data['assets']) > 0)
                                <tr style="background:#DBEAFE;">
                                    <td colspan="3"><strong>ASET</strong></td>
                                </tr>
                                @foreach($data['assets'] as $group)
                                    <tr style="background:#EFF6FF;">
                                        <td colspan="3"><strong>{{ $group['label'] }}</strong></td>
                                    </tr>
                                    @foreach($group['items'] as $item)
                                        <tr>
                                            <td style="padding-left:24px;">{{ $item->code }} - {{ $item->name }}</td>
                                            <td class="text-right">Rp {{ number_format($item->debit, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp {{ number_format($item->credit, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                    <tr style="background:#F8FAFC;">
                                        <td style="padding-left:20px;"><strong>Subtotal {{ $group['label'] }}</strong></td>
                                        <td class="text-right"><strong>Rp {{ number_format($group['subtotal_debit'], 0, ',', '.') }}</strong></td>
                                        <td class="text-right"><strong>Rp {{ number_format($group['subtotal_credit'], 0, ',', '.') }}</strong></td>
                                    </tr>
                                @endforeach
                            @endif

                            @if(count($data['liabilities']) > 0)
                                <tr style="background:#DBEAFE;">
                                    <td colspan="3"><strong>LIABILITAS</strong></td>
                                </tr>
                                @foreach($data['liabilities'] as $group)
                                    <tr style="background:#EFF6FF;">
                                        <td colspan="3"><strong>{{ $group['label'] }}</strong></td>
                                    </tr>
                                    @foreach($group['items'] as $item)
                                        <tr>
                                            <td style="padding-left:24px;">{{ $item->code }} - {{ $item->name }}</td>
                                            <td class="text-right">Rp {{ number_format($item->debit, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp {{ number_format($item->credit, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                    <tr style="background:#F8FAFC;">
                                        <td style="padding-left:20px;"><strong>Subtotal {{ $group['label'] }}</strong></td>
                                        <td class="text-right"><strong>Rp {{ number_format($group['subtotal_debit'], 0, ',', '.') }}</strong></td>
                                        <td class="text-right"><strong>Rp {{ number_format($group['subtotal_credit'], 0, ',', '.') }}</strong></td>
                                    </tr>
                                @endforeach
                            @endif

                            @if(count($data['equities']) > 0)
                                <tr style="background:#DBEAFE;">
                                    <td colspan="3"><strong>MODAL</strong></td>
                                </tr>
                                @foreach($data['equities'] as $group)
                                    <tr style="background:#EFF6FF;">
                                        <td colspan="3"><strong>{{ $group['label'] }}</strong></td>
                                    </tr>
                                    @foreach($group['items'] as $item)
                                        <tr>
                                            <td style="padding-left:24px;">{{ $item->code }} - {{ $item->name }}</td>
                                            <td class="text-right">Rp {{ number_format($item->debit, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp {{ number_format($item->credit, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                    <tr style="background:#F8FAFC;">
                                        <td style="padding-left:20px;"><strong>Subtotal {{ $group['label'] }}</strong></td>
                                        <td class="text-right"><strong>Rp {{ number_format($group['subtotal_debit'], 0, ',', '.') }}</strong></td>
                                        <td class="text-right"><strong>Rp {{ number_format($group['subtotal_credit'], 0, ',', '.') }}</strong></td>
                                    </tr>
                                @endforeach
                            @endif

                            <tr style="background:#DBEAFE;">
                                <td><strong>Total Debit = Total Kredit</strong></td>
                                <td class="text-right"><strong>Rp {{ number_format($data['total_debit'], 0, ',', '.') }}</strong></td>
                                <td class="text-right"><strong>Rp {{ number_format($data['total_credit'], 0, ',', '.') }}</strong></td>
                            </tr>
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