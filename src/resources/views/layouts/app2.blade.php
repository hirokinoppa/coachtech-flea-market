<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'coachtechフリマ')</title>
    @yield('css')
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app-common.css') }}">
</head>
<body class="@yield('body-class', 'app-page')">
    <header class="site-header">
        <div class="site-header__inner">
            <a
                class="site-header__logo-link"
                href="{{ url('/') }}"
                aria-label="トップページへ"
            >
                <img
                    src="{{ asset('images/coachtech-logo.png') }}"
                    alt="COACHTECH"
                    class="site-header__logo"
                >
            </a>

            <form class="site-header__search" method="GET" action="{{ url('/') }}">
                <label class="site-header__search-label" for="keyword">検索</label>
                <input
                    class="site-header__search-input"
                    type="text"
                    name="keyword"
                    id="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="なにをお探しですか？"
                >
            </form>

            <nav class="site-header__nav" aria-label="ヘッダーナビゲーション">
                @auth
                    <form class="site-header__nav-item" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="site-header__nav-link" type="submit">ログアウト</button>
                    </form>

                    <a class="site-header__nav-link" href="{{ route('profile.edit') }}">マイページ</a>
                    <a class="site-header__nav-button" href="{{ url('/goods/create') }}">出品</a>
                @endauth

                @guest
                    <a class="site-header__nav-link" href="{{ route('login') }}">ログイン</a>
                    <a class="site-header__nav-link" href="{{ route('register') }}">会員登録</a>
                @endguest
            </nav>
        </div>
    </header>

    <main class="app-main">
        @yield('content')
    </main>
</body>
</html>