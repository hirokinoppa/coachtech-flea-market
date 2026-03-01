@extends('layouts.app2')

@section('title', ($item->name ?? '購入画面') . '｜購入')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')
<div class="purchase">

    <div class="purchase__wrap">

        {{-- 左カラム --}}
        <div class="purchase__left">

            {{-- 商品サマリ（画像＋名前＋価格） --}}
            <div class="purchase-item">
                <div class="purchase-item__image">
                    @if(!empty($item->image_path))
                        <img src="{{ $item->image_path }}" alt="{{ $item->name }}">
                    @else
                        <div class="purchase-item__placeholder">商品画像</div>
                    @endif
                </div>

                <div class="purchase-item__meta">
                    <h1 class="purchase-item__name">{{ $item->name ?? '' }}</h1>
                    <p class="purchase-item__price">¥{{ number_format((int)($item->price ?? 0)) }}</p>
                </div>
            </div>

            <hr class="purchase__line">

            {{-- ここが購入確定（purchase.store） --}}
            <form
                id="purchase-form"
                method="POST"
                action="{{ route('purchase.store', ['item_id' => $item->id]) }}"
                class="purchase-form"
            >
                @csrf

                {{-- 購入不可系エラー（出品者/売り切れ等） --}}
                @error('purchase')
                    <p class="purchase-form__error">{{ $message }}</p>
                @enderror

                {{-- 配送先未設定エラー（PurchaseRequest の shipping エラー） --}}
                @error('shipping')
                    <p class="purchase-form__error">{{ $message }}</p>
                @enderror

                {{-- 支払い方法 --}}
                <section class="purchase-section">
                    <h2 class="purchase-section__title">支払い方法</h2>

                    <div class="purchase-form__field">
                        <select
                            id="payment-method"
                            name="payment_method"
                            class="purchase-form__select @error('payment_method') is-invalid @enderror"
                            required
                        >
                            <option value="" {{ old('payment_method') === null ? 'selected' : '' }}>
                                選択してください
                            </option>
                            <option value="convenience" {{ old('payment_method') === 'convenience' ? 'selected' : '' }}>
                                コンビニ払い
                            </option>
                            <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>
                                カード支払い
                            </option>
                        </select>

                        @error('payment_method')
                            <p class="purchase-form__error">{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                <hr class="purchase__line">

                {{-- 配送先 --}}
                <section class="purchase-section">
                    <div class="purchase-section__head">
                        <h2 class="purchase-section__title">配送先</h2>

                        <a class="purchase-section__link"
                            href="{{ route('purchase.address.edit', ['item_id' => $item->id]) }}">
                            変更する
                        </a>
                    </div>

                    <div class="purchase-address">
                        <p class="purchase-address__postal">
                            〒 {{ $shipping['postal_code'] ?? 'XXX-YYYY' }}
                        </p>

                        <p class="purchase-address__text">
                            {{ $shipping['address'] ?? 'ここには住所が入ります' }}
                            {{ !empty($shipping['building']) ? $shipping['building'] : 'ここには建物が入ります' }}
                        </p>
                    </div>
                </section>

                <hr class="purchase__line">
            </form>

        </div>

        {{-- 右カラム（サマリー） --}}
        <aside class="purchase__right">

            <div class="purchase-summary">
                <div class="purchase-summary__row">
                    <div class="purchase-summary__key">商品代金</div>
                    <div class="purchase-summary__val">¥{{ number_format((int)($item->price ?? 0)) }}</div>
                </div>

                <div class="purchase-summary__row purchase-summary__row--border">
                    <div class="purchase-summary__key">支払い方法</div>
                    <div class="purchase-summary__val" id="payment-method-label">
                        {{ old('payment_method') === 'card'
                            ? 'カード支払い'
                            : (old('payment_method') === 'convenience'
                                ? 'コンビニ払い'
                                : '選択してください') }}
                    </div>
                </div>
            </div>

            {{-- 右の購入ボタンで左の form を送信（JS不要） --}}
            <button
                type="submit"
                class="purchase__buy"
                form="purchase-form"
            >
                購入する
            </button>

        </aside>

    </div>

</div>

<script>
    (function () {
    const select = document.getElementById('payment-method');
    const label  = document.getElementById('payment-method-label');

    if (!select || !label) return;

    const map = {
        convenience: 'コンビニ払い',
        card: 'カード支払い',
        '': '選択してください'
    };

    const render = () => {
        label.textContent = map[select.value] ?? '選択してください';
    };

    select.addEventListener('change', render);
    render();
    })();
</script>

@endsection