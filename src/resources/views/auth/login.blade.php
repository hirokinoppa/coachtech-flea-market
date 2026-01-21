@extends('layouts.app')

@section('title', 'ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('body-class', 'auth-page login-page')

@section('content')
<section class="login-card" aria-labelledby="login-title">
    <h1 class="login-card__title" id="login-title">ログイン</h1>

    <form class="login-form" method="POST" action="{{ route('login') }}" novalidate>
        @csrf

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
                autocomplete="current-password"
                required
            >
            @error('password')
                <p class="form-field__error">{{ $message }}</p>
            @enderror
        </div>

        <button class="login-form__submit" type="submit">ログインする</button>

        <div class="login-card__footer">
            <a class="login-card__link" href="{{ route('register') }}">会員登録はこちら</a>
        </div>
    </form>
</section>
@endsection