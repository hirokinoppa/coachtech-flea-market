@extends('layouts.app2')

@section('title', '商品一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
@php
    $tab = request('tab');
    $keyword = request('keyword');
@endphp

<div class="items-page">

    {{-- タブ --}}
    <div class="items-tabs">
        <div class="items-tabs__inner">
            <a href="{{ url('/') . ($keyword ? ('?keyword=' . urlencode($keyword)) : '') }}"
               class="items-tabs__tab {{ $tab !== 'mylist' ? 'items-tabs__tab--active' : '' }}">
                おすすめ
            </a>

            <a href="{{ url('/') . '?tab=mylist' . ($keyword ? ('&keyword=' . urlencode($keyword)) : '') }}"
               class="items-tabs__tab {{ $tab === 'mylist' ? 'items-tabs__tab--active' : '' }}">
                マイリスト
            </a>
        </div>
    </div>

    {{-- 一覧 --}}
    <div class="items-grid">
        @forelse($items as $item)
            @php
                // image_path が
                // 1) items/xxx.jpg なら Storage::url で /storage/items/xxx.jpg に変換
                // 2) すでに /storage/... や http(s)://... ならそのまま使う
                $img = $item->image_path ?? '';
                if (!empty($img)) {
                    if (!\Illuminate\Support\Str::startsWith($img, ['http://', 'https://', '/'])) {
                        $img = \Illuminate\Support\Facades\Storage::url($img);
                    }
                }
            @endphp

            <a class="item-card {{ $item->is_sold ? 'item-card--sold' : '' }}"
                href="{{ route('items.show', ['item_id' => $item->id]) }}">

                <div class="item-card__img">
                    @if(!empty($img))
                        <img src="{{ $img }}" alt="{{ $item->name }}">
                    @else
                        <div class="item-card__img--placeholder">商品画像</div>
                    @endif

                    @if($item->is_sold)
                        <span class="item-card__sold">Sold</span>
                    @endif
                </div>

                <div class="item-card__name">{{ $item->name }}</div>

                {{-- カテゴリ表示（複数カテゴリ対応） --}}
                @if(isset($item->categories) && $item->categories->isNotEmpty())
                    <div class="item-card__categories">
                        @foreach($item->categories->take(2) as $category)
                            <span class="item-card__tag">{{ $category->name }}</span>
                        @endforeach

                        @if($item->categories->count() > 2)
                            <span class="item-card__tag">+{{ $item->categories->count() - 2 }}</span>
                        @endif
                    </div>
                @endif
            </a>
        @empty
            <p class="items-empty">商品がありません</p>
        @endforelse
    </div>

</div>
@endsection