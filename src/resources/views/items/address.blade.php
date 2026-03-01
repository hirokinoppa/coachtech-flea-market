@extends('layouts.app2')

@section('title', '住所の変更')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/address.css') }}">
@endsection

@section('content')
<div class="address">
    <h1 class="address__title">住所の変更</h1>

    <form
        class="address__form"
        method="POST"
        action="{{ route('purchase.address.update', ['item_id' => $item->id]) }}"
        novalidate
    >
        @csrf

        <div class="address__field">
            <label class="address__label" for="postal_code">郵便番号</label>
            <input
                class="address__input @error('postal_code') is-invalid @enderror"
                type="text"
                id="postal_code"
                name="postal_code"
                value="{{ old('postal_code', $shipping['postal_code'] ?? '') }}"
                placeholder="123-4567"
                required
            >
            @error('postal_code')
                <p class="address__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="address__field">
            <label class="address__label" for="address">住所</label>
            <input
                class="address__input @error('address') is-invalid @enderror"
                type="text"
                id="address"
                name="address"
                value="{{ old('address', $shipping['address'] ?? '') }}"
                required
            >
            @error('address')
                <p class="address__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="address__field">
            <label class="address__label" for="building">建物名</label>
            <input
                class="address__input @error('building') is-invalid @enderror"
                type="text"
                id="building"
                name="building"
                value="{{ old('building', $shipping['building'] ?? '') }}"
            >
            @error('building')
                <p class="address__error">{{ $message }}</p>
            @enderror
        </div>

        <button class="address__submit" type="submit">更新する</button>
    </form>
</div>
@endsection