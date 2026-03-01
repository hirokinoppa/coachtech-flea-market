@extends('layouts.app2')

@section('title', 'マイページ')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

@section('content')
<div class="mypage">

    <section class="mypage__head">
        <div class="mypage__avatar">
            @if(!empty($profile?->image_path))
                <img src="{{ asset('storage/'.$profile->image_path) }}" alt="プロフィール画像">
            @else
                <div class="mypage__avatar--placeholder"></div>
            @endif
        </div>

        <div class="mypage__info">
            <h1 class="mypage__name">{{ $profile?->name ?? $user->name ?? 'ユーザー名' }}</h1>
        </div>

        <div class="mypage__action">
            <a class="mypage__edit" href="{{ route('profile.edit') }}">プロフィールを編集</a>
        </div>
    </section>

    {{-- タブ（JSなし：ラジオで切替） --}}
    <div class="mypage__tabs">
        <input type="radio" name="tab" id="tab-sell" checked>
        <label for="tab-sell" class="mypage__tab">出品した商品</label>

        <input type="radio" name="tab" id="tab-buy">
        <label for="tab-buy" class="mypage__tab">購入した商品</label>

        <div class="mypage__panel mypage__panel--sell">
            <div class="mypage__grid">
                @forelse($sellItems as $item)
                    @php
                        $path = $item->image_path ?? null;

                        $imageUrl = null;
                        if (!empty($path)) {
                            $imageUrl = \Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])
                                ? $path
                                : asset('storage/' . ltrim($path, '/'));
                        }
                    @endphp

                    <a class="mypage__card" href="{{ route('items.show', ['item_id' => $item->id]) }}">
                        <div class="mypage__thumb">
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $item->name }}">
                            @else
                                <div class="mypage__thumb--placeholder">商品画像</div>
                            @endif

                            @if(!empty($item->is_sold))
                                <span class="mypage__badge">SOLD</span>
                            @endif
                        </div>
                        <p class="mypage__item-name">{{ $item->name }}</p>
                    </a>
                @empty
                    <p class="mypage__empty">出品した商品はありません。</p>
                @endforelse
            </div>
        </div>

        <div class="mypage__panel mypage__panel--buy">
            <div class="mypage__grid">
                @forelse($buyOrders as $order)
                    @php
                        $item = $order->item;

                        $path = $item?->image_path ?? null;
                        $imageUrl = null;

                        if (!empty($path)) {
                            $imageUrl = \Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])
                                ? $path
                                : asset('storage/' . ltrim($path, '/'));
                        }
                    @endphp

                    @if($item)
                        <a class="mypage__card" href="{{ route('items.show', ['item_id' => $item->id]) }}">
                            <div class="mypage__thumb">
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $item->name }}">
                                @else
                                    <div class="mypage__thumb--placeholder">商品画像</div>
                                @endif

                                <span class="mypage__badge">購入済み</span>
                            </div>
                            <p class="mypage__item-name">{{ $item->name }}</p>
                        </a>
                    @endif
                @empty
                    <p class="mypage__empty">購入した商品はありません。</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection