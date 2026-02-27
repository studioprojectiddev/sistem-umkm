@extends('layouts.app')

@section('title', 'Bank / E-wallet')

@section('content')
<h1 class="title">Bank / E-wallet</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.transaction.income') }}" class="active">Bank / E-wallet</a></li>
</ul>

<div class="info-data">
    <div class="card">

    </div>
</div>

@endsection