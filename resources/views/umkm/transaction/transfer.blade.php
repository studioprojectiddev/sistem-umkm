@extends('layouts.app')

@section('title','Data Terhapus')

@section('content')

<form method="POST" action="{{ route('umkm.transaction.store_transfer') }}">
    @csrf

    <select name="from_account_id" required>
        <option value="">Dari Rekening</option>
        @foreach($accounts as $a)
            <option value="{{ $a->id }}">{{ $a->name }}</option>
        @endforeach
    </select>

    <select name="to_account_id" required>
        <option value="">Ke Rekening</option>
        @foreach($accounts as $a)
            <option value="{{ $a->id }}">{{ $a->name }}</option>
        @endforeach
    </select>

    <input type="number" name="amount" placeholder="Nominal" required>
    <input type="date" name="transfer_date" required>

    <button type="submit">Transfer</button>

</form>

@endsection