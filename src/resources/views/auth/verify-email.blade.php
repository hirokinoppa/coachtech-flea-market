@extends('layouts.app')

@section('title', 'メール認証')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
<div class="verify-email">

    {{-- フラッシュ（再送成功など） --}}
    @if (session('message'))
        <p class="verify-email__flash">
            {{ session('message') }}
        </p>
    @endif

    <p class="verify-email__text">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    {{-- 認証はこちらから（MailHog を開く想定：localhost:8025） --}}
    <a
        class="verify-email__btn"
        href="http://localhost:8025"
        target="_blank"
        rel="noopener noreferrer"
    >
        認証はこちらから
    </a>

    {{-- 認証メール再送 --}}
    <form method="POST" action="{{ route('verification.send') }}" class="verify-email__resend">
        @csrf
        <button type="submit" class="verify-email__link">
            認証メールを再送する
        </button>
    </form>

</div>
@endsection