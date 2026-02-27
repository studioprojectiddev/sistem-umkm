@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
<h1 class="title">Riwayat Transaksi</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.transaction.history') }}" class="active">Riwayat Transaksi</a></li>
</ul>

<div class="info-data">
    <div class="card">

    </div>
</div>

@endsection