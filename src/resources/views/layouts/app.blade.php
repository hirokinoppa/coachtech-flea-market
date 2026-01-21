<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'coachtechフリマ')</title>
    @yield('css')
    <link rel="stylesheet" href="{{ asset('css/auth-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
</head>
<body class="@yield('body-class', 'auth-page')">
    <header class="auth-header">
        <div class="auth-header__inner">
            <a
                class="auth-header__logo-link"
                href="{{ url('/') }}"
                aria-label="トップページへ"
            >
                <img
                    src="{{ asset('images/coachtech-logo.png') }}"
                    alt="COACHTECH"
                    class="auth-header__logo"
                >
            </a>
        </div>
    </header>

    <main class="auth-main">
        @yield('content')
    </main>
</body>
</html>