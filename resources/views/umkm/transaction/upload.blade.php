@extends('layouts.app')

@section('title', 'Upload Nota (OCR)')

@section('content')
<h1 class="title">Upload Nota (OCR)</h1>
<ul class="breadcrumbs">
    <li><a href="{{ route('dashboard') }}">Home</a></li>
    <li>/</li>
    <li><a href="{{ route('umkm.transaction.upload') }}" class="active">Upload Nota (OCR)</a></li>
</ul>

<div class="info-data">
    <div class="card">

    </div>
</div>

@endsection