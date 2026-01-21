@extends('layouts.app')

@section('title', '会員登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('body-class', 'auth-page register-page')

@section('content')
<section class="register-card" aria-labelledby="register-title">
    <h1 class="register-card__title" id="register-title">会員登録</h1>

    <form class="register-form" method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="form-field">
            <label class="form-field__label" for="name">ユーザー名</label>
            <input
                class="form-field__input @error('name') form-field__input--invalid @enderror"
                type="text"
                name="name"
                id="name"
                value="{{ old('name') }}"
                autocomplete="name"
                required
            >
            @error('name')
                <p class="form-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label class="form-field__label" for="email">メールアドレス</label>
            <input
                class="form-field__input @error('email') form-field__input--invalid @enderror"
                type="email"
                name="email"
                id="email"
                value="{{ old('email') }}"
                autocomplete="email"
                required
            >
            @error('email')
                <p class="form-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label class="form-field__label" for="password">パスワード</label>
            <input
                class="form-field__input @error('password') form-field__input--invalid @enderror"
                type="password"
                name="password"
                id="password"
                autocomplete="new-password"
                required
            >
            @error('password')
                <p class="form-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label class="form-field__label" for="password_confirmation">確認用パスワード</label>
            <input
                class="form-field__input
                    @error('password') form-field__input--invalid @enderror
                    @error('password_confirmation') form-field__input--invalid @enderror"
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                autocomplete="new-password"
                required
            >
            @error('password_confirmation')
                <p class="form-field__error">{{ $message }}</p>
            @enderror
        </div>

        <button class="register-form__submit" type="submit">登録する</button>

        <div class="register-card__footer">
            <a class="register-card__link" href="{{ route('login') }}">ログインはこちら</a>
        </div>
    </form>
</section>
@endsection