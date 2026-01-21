@extends('layouts.app2')

@section('title', 'プロフィール設定')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('body-class', 'profile-page')

@section('content')
<section class="profile-card" aria-labelledby="profile-title">
    <h1 class="profile-card__title" id="profile-title">プロフィール設定</h1>

    <form
        class="profile-form"
        method="POST"
        action="{{ route('mypage.update') }}"
        enctype="multipart/form-data"
        novalidate
    >
        @csrf

        <div class="profile-avatar">
            <div class="profile-avatar__preview">
                @if (!empty($profile->image_path))
                    <img
                        class="profile-avatar__image"
                        src="{{ asset('storage/' . $profile->image_path) }}"
                        alt="プロフィール画像"
                    >
                @else
                    <div class="profile-avatar__placeholder" aria-hidden="true"></div>
                @endif
            </div>

            <label class="profile-avatar__button" for="image">
                画像を選択する
            </label>

            <input
                class="profile-avatar__input @error('image') profile-avatar__input--invalid @enderror"
                type="file"
                name="image"
                id="image"
                accept="image/jpeg,image/png"
            >

            @error('image')
                <p class="form-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label class="form-field__label" for="name">ユーザー名</label>
            <input
                class="form-field__input @error('name') form-field__input--invalid @enderror"
                type="text"
                name="name"
                id="name"
                value="{{ old('name', $profile->name) }}"
                required
            >
            @error('name')
                <p class="form-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label class="form-field__label" for="postal_code">郵便番号</label>
            <input
                class="form-field__input @error('postal_code') form-field__input--invalid @enderror"
                type="text"
                name="postal_code"
                id="postal_code"
                value="{{ old('postal_code', $profile->postal_code) }}"
                placeholder="123-4567"
                required
            >
            @error('postal_code')
                <p class="form-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label class="form-field__label" for="address">住所</label>
            <input
                class="form-field__input @error('address') form-field__input--invalid @enderror"
                type="text"
                name="address"
                id="address"
                value="{{ old('address', $profile->address) }}"
                required
            >
            @error('address')
                <p class="form-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label class="form-field__label" for="building">建物名</label>
            <input
                class="form-field__input @error('building') form-field__input--invalid @enderror"
                type="text"
                name="building"
                id="building"
                value="{{ old('building', $profile->building) }}"
            >
            @error('building')
                <p class="form-field__error">{{ $message }}</p>
            @enderror
        </div>

        <button class="profile-form__submit" type="submit">更新する</button>
    </form>
</section>
@endsection