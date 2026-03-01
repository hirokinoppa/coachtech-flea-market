@extends('layouts.app2')

@section('title', ($item->name ?? '商品詳細') . '｜商品詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/item-show.css') }}">
@endsection

@section('content')
@php
    use Illuminate\Support\Str;

    // ✅ 商品画像：URL か storage 相対パスかを吸収
    $itemImagePath = $item->image_path ?? null;
    $itemImageUrl = null;

    if (!empty($itemImagePath)) {
        $itemImageUrl = Str::startsWith($itemImagePath, ['http://', 'https://'])
            ? $itemImagePath
            : asset('storage/' . ltrim($itemImagePath, '/'));
    }
@endphp

<div class="item-show">

    <div class="item-show__top">
        {{-- 左：商品画像 --}}
        <div class="item-show__image">
            @if($itemImageUrl)
                <img src="{{ $itemImageUrl }}" alt="{{ $item->name }}">
            @else
                <div class="item-show__image--placeholder">商品画像</div>
            @endif
        </div>

        {{-- 右：商品情報 --}}
        <div class="item-show__main">
            <h1 class="item-show__name">{{ $item->name }}</h1>

            {{-- ブランド名 --}}
            @if(!empty($item->brand))
                <p class="item-show__brand">{{ $item->brand }}</p>
            @endif

            {{-- 価格 --}}
            <p class="item-show__price">
                ¥{{ number_format((int) $item->price) }} <span class="item-show__price-tax">(税込)</span>
            </p>

            {{-- いいね数 / コメント数 --}}
            <div class="item-show__counts">

                @auth
                    <form method="POST" action="{{ route('items.like', ['item_id' => $item->id]) }}">
                        @csrf
                        <button type="submit" class="item-show__count item-show__count--like" aria-label="いいね">
                            <img
                                class="item-show__count-icon"
                                src="{{ asset($isLiked ? 'images/heart-liked.png' : 'images/heart-default.png') }}"
                                alt=""
                            >
                            <span class="item-show__count-num">{{ $item->likes_count ?? 0 }}</span>
                        </button>
                    </form>
                @endauth

                @guest
                    <a
                        class="item-show__count item-show__count--like"
                        href="{{ route('login', ['redirect' => url()->full()]) }}"
                        aria-label="いいね（ログインが必要）"
                    >
                        <img class="item-show__count-icon" src="{{ asset('images/heart-default.png') }}" alt="">
                        <span class="item-show__count-num">{{ $item->likes_count ?? 0 }}</span>
                    </a>
                @endguest

                {{-- コメント数 --}}
                <div class="item-show__count item-show__count--comment" aria-label="コメント数">
                    <img class="item-show__count-icon" src="{{ asset('images/comment.png') }}" alt="">
                    <span class="item-show__count-num">{{ $item->comments_count ?? 0 }}</span>
                </div>
            </div>

            {{-- 購入導線 --}}
            <div class="item-show__cta">
                @guest
                    <a class="item-show__buy" href="{{ route('register') }}">購入手続きへ</a>
                @endguest

                @auth
                    @if($canPurchase ?? false)
                        <a class="item-show__buy" href="{{ route('purchase.show', ['item_id' => $item->id]) }}">
                            購入手続きへ
                        </a>
                    @else
                        <button class="item-show__buy item-show__buy--disabled" type="button" disabled>
                            購入手続きへ
                        </button>
                    @endif
                @endauth
            </div>

            {{-- 商品説明 --}}
            <section class="item-show__section">
                <h2 class="item-show__section-title">商品説明</h2>
                <p class="item-show__desc">{{ $item->description }}</p>
            </section>

            {{-- 商品の情報 --}}
            <section class="item-show__section">
                <h2 class="item-show__section-title">商品の情報</h2>

                <dl class="item-show__info">
                    {{-- カテゴリー（複数） --}}
                    <div class="item-show__info-row">
                        <dt class="item-show__info-key">カテゴリー</dt>
                        <dd class="item-show__info-val">
                            @if($item->categories && $item->categories->count())
                                @foreach($item->categories as $category)
                                    <span class="item-show__tag">{{ $category->name }}</span>
                                @endforeach
                            @else
                                <span class="item-show__tag">未設定</span>
                            @endif
                        </dd>
                    </div>

                    {{-- 商品の状態 --}}
                    <div class="item-show__info-row">
                        <dt class="item-show__info-key">商品の状態</dt>
                        <dd class="item-show__info-val">
                            @php
                                $conditionLabelMap = [
                                    1 => '良好',
                                    2 => '目立った傷や汚れなし',
                                    3 => 'やや傷や汚れあり',
                                    4 => '状態が悪い',
                                    'good' => '良好',
                                    'fair' => '目立った傷や汚れなし',
                                    'poor' => 'やや傷や汚れあり',
                                    'bad'  => '状態が悪い',
                                ];
                                $conditionLabel = $conditionLabelMap[$item->condition] ?? (string) $item->condition;
                            @endphp
                            {{ $conditionLabel }}
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- コメント --}}
            <section class="item-show__section">
                <h2 class="item-show__section-title">
                    コメント({{ $item->comments_count ?? 0 }})
                </h2>

                <div class="item-show__comments">
                    @forelse($item->comments as $comment)
                        @php
                            $commentUser = $comment->user;
                            $profile = optional($commentUser)->profile;
                            $avatarPath = $profile->image_path ?? null;

                            $avatarUrl = null;
                            if (!empty($avatarPath)) {
                                $avatarUrl = Str::startsWith($avatarPath, ['http://', 'https://'])
                                    ? $avatarPath
                                    : asset('storage/' . ltrim($avatarPath, '/'));
                            }
                        @endphp

                        <div class="comment">
                            <div class="comment__header">
                                <div class="comment__avatar">
                                    @if($avatarUrl)
                                        <img class="comment__avatar-img" src="{{ $avatarUrl }}" alt="">
                                    @endif
                                </div>

                                <div class="comment__user">
                                    {{ optional($commentUser)->name ?? 'ユーザー' }}
                                </div>
                            </div>

                            <div class="comment__body">
                                {{ $comment->body }}
                            </div>
                        </div>
                    @empty
                        <p class="item-show__empty">コメントはまだありません</p>
                    @endforelse
                </div>

                {{-- コメント投稿 --}}
                <div class="item-show__comment-form">
                    <h3 class="item-show__comment-title">商品へのコメント</h3>

                    @auth
                        <form method="POST" action="{{ route('comments.store', ['item_id' => $item->id]) }}">
                            @csrf
                            <textarea
                                class="item-show__textarea @error('body') is-invalid @enderror"
                                name="body"
                                rows="6"
                            >{{ old('body') }}</textarea>

                            @error('body')
                                <p class="item-show__error">{{ $message }}</p>
                            @enderror

                            <button class="item-show__comment-submit" type="submit">
                                コメントを送信する
                            </button>
                        </form>
                    @endauth
                    @guest
                        <form method="GET" action="{{ route('login') }}">
                            <input type="hidden" name="redirect" value="{{ url()->full() }}">

                            <textarea class="item-show__textarea" name="content_preview" rows="6"></textarea>

                            <button class="item-show__comment-submit" type="submit">
                                コメントを送信する
                            </button>
                        </form>
                    @endguest
                </div>
            </section>
        </div>
    </div>

</div>
@endsection