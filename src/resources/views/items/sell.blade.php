@extends('layouts.app2')

@section('title', '商品の出品')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/sell.css') }}">
@endsection

@section('content')
<div class="sell">
    <div class="sell__container">

        <h1 class="sell__title">商品の出品</h1>

        <form class="sell-form" method="POST" action="{{ route('sell.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- 商品画像 --}}
            <section class="sell-block">
                <h2 class="sell-block__title">商品画像</h2>

                <div class="sell-image">
                    <div class="sell-image__box" id="sellImageBox">
                        <div class="sell-image__preview" id="sellImagePreview">
                            {{-- 画像は選択後にJSでプレビュー表示 --}}
                        </div>

                        <label class="sell-image__button" for="image">
                            画像を選択する
                        </label>

                        <input
                            type="file"
                            name="image"
                            id="image"
                            class="sell-image__input @error('image') is-invalid @enderror"
                            accept="image/jpeg,image/jpg,image/png"
                        >
                    </div>

                    @error('image')
                        <p class="sell-error">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            {{-- 商品の詳細 --}}
            <section class="sell-block">
                <h2 class="sell-block__title sell-block__title--line">商品の詳細</h2>

                {{-- カテゴリー（複数選択） --}}
                <div class="sell-field">
                    <label class="sell-label">カテゴリー</label>

                    @if($errors->has('category_ids') || $errors->has('category_ids.*'))
                        <p class="sell-error">
                            {{ $errors->first('category_ids') ?: $errors->first('category_ids.*') }}
                        </p>
                    @endif

                    @php
                        $oldCategoryIds = collect(old('category_ids', []))->map(fn($v) => (int)$v)->all();
                    @endphp

                    <div class="sell-categories">
                        @foreach($categories as $category)
                            <label class="sell-chip">
                                <input
                                    type="checkbox"
                                    name="category_ids[]"
                                    value="{{ $category->id }}"
                                    {{ in_array($category->id, $oldCategoryIds, true) ? 'checked' : '' }}
                                >
                                <span>{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- 商品の状態 --}}
                <div class="sell-field">
                    <label class="sell-label" for="condition">商品の状態</label>

                    <select
                        name="condition"
                        id="condition"
                        class="sell-select @error('condition') is-invalid @enderror"
                    >
                        <option value="" {{ old('condition', '') === '' ? 'selected' : '' }}>
                            選択してください
                        </option>
                        <option value="good" {{ old('condition') === 'good' ? 'selected' : '' }}>良好</option>
                        <option value="fair" {{ old('condition') === 'fair' ? 'selected' : '' }}>目立った傷や汚れなし</option>
                        <option value="poor" {{ old('condition') === 'poor' ? 'selected' : '' }}>やや傷や汚れあり</option>
                        <option value="bad"  {{ old('condition') === 'bad'  ? 'selected' : '' }}>状態が悪い</option>
                    </select>

                    @error('condition')
                        <p class="sell-error">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            {{-- 商品名と説明 --}}
            <section class="sell-block">
                <h2 class="sell-block__title sell-block__title--line">商品名と説明</h2>

                {{-- 商品名 --}}
                <div class="sell-field">
                    <label class="sell-label" for="name">商品名</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="sell-input @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                    >
                    @error('name')
                        <p class="sell-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ブランド名 --}}
                <div class="sell-field">
                    <label class="sell-label" for="brand">ブランド名</label>
                    <input
                        type="text"
                        name="brand"
                        id="brand"
                        class="sell-input @error('brand') is-invalid @enderror"
                        value="{{ old('brand') }}"
                    >
                    @error('brand')
                        <p class="sell-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 説明 --}}
                <div class="sell-field">
                    <label class="sell-label" for="description">商品の説明</label>
                    <textarea
                        name="description"
                        id="description"
                        class="sell-textarea @error('description') is-invalid @enderror"
                        rows="6"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="sell-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 販売価格 --}}
                <div class="sell-field">
                    <label class="sell-label" for="price">販売価格</label>
                    <div class="sell-price">
                        <span class="sell-price__yen">¥</span>
                        <input
                            type="text"
                            name="price"
                            id="price"
                            class="sell-input sell-input--price @error('price') is-invalid @enderror"
                            value="{{ old('price') }}"
                            inputmode="numeric"
                        >
                    </div>
                    @error('price')
                        <p class="sell-error">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <button type="submit" class="sell-submit">出品する</button>
        </form>
    </div>
</div>

{{-- 画像プレビュー --}}
<script>
(() => {
  const input = document.getElementById('image');
  const preview = document.getElementById('sellImagePreview');
  const box = document.getElementById('sellImageBox');

  if (!input || !preview || !box) return;

  let currentUrl = null;

  input.addEventListener('change', (e) => {
    const file = e.target.files?.[0];

    if (!file) {
      preview.innerHTML = '';
      box.classList.remove('has-image');
      preview.classList.add('sell-image__preview--empty');
      if (currentUrl) URL.revokeObjectURL(currentUrl);
      currentUrl = null;
      return;
    }

    if (!file.type.startsWith('image/')) return;

    if (currentUrl) URL.revokeObjectURL(currentUrl);
    currentUrl = URL.createObjectURL(file);

    preview.innerHTML = `<img src="${currentUrl}" alt="選択した画像">`;

    box.classList.add('has-image');
    preview.classList.remove('sell-image__preview--empty');
  });
})();
</script>
@endsection