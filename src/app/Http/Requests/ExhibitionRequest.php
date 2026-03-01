<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 商品名：必須
            'name' => ['required', 'string', 'max:255'],

            // 商品説明：必須、最大255
            'description' => ['required', 'string', 'max:255'],

            // 商品画像：必須、拡張子は jpeg or png（要件準拠で jpg は除外）
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png', 'max:5120'],

            // 商品のカテゴリー：必須（複数選択）
            'category_ids'   => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],

            // 商品の状態：必須（プルダウンと一致させる）
            'condition' => ['required', 'in:good,fair,poor,bad'],

            // 商品価格：必須、数値、0円以上
            'price' => ['required', 'integer', 'min:0'],

            // ブランド名：任意
            'brand' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '商品名を入力してください',
            'name.max'      => '商品名は255文字以内で入力してください',

            'description.required' => '商品の説明を入力してください',
            'description.max'      => '商品の説明は255文字以内で入力してください',

            'image.required' => '商品画像をアップロードしてください',
            'image.image'    => '商品画像は画像ファイルを選択してください',
            'image.mimes'    => '商品画像はjpegまたはpng形式でアップロードしてください',
            'image.max'      => '商品画像は5MB以内でアップロードしてください',

            'category_ids.required'   => 'カテゴリーを選択してください',
            'category_ids.array'      => 'カテゴリーの選択が不正です',
            'category_ids.min'        => 'カテゴリーを1つ以上選択してください',
            'category_ids.*.integer'  => 'カテゴリーの選択が不正です',
            'category_ids.*.exists'   => '選択したカテゴリーが存在しません',

            'condition.required' => '商品の状態を選択してください',
            'condition.in'       => '商品の状態の選択が不正です',

            'price.required' => '販売価格を入力してください',
            'price.integer'  => '販売価格は数値で入力してください',
            'price.min'      => '販売価格は0円以上で入力してください',

            'brand.max' => 'ブランド名は255文字以内で入力してください',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'         => '商品名',
            'description'  => '商品の説明',
            'image'        => '商品画像',
            'category_ids' => 'カテゴリー',
            'condition'    => '商品の状態',
            'price'        => '販売価格',
            'brand'        => 'ブランド名',
        ];
    }
}