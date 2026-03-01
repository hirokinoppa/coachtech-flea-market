<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'in:convenience,card'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => '支払い方法を選択してください',
            'payment_method.in'       => '支払い方法を正しく選択してください',
        ];
    }

    /**
     * ✅ 配送先チェック（購入中に変更した住所 = session を優先）
     * - session: purchase.shipping.{item_id}
     * - なければ profile を使う
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            // ルートパラメータ item_id を取得（/purchase/{item_id}）
            $itemId = (int) $this->route('item_id');

            // ① session の配送先（購入中だけの変更）を優先
            $shipping = session("purchase.shipping.{$itemId}");

            if (is_array($shipping)) {
                $postal = trim((string)($shipping['postal_code'] ?? ''));
                $addr   = trim((string)($shipping['address'] ?? ''));

                if ($postal !== '' && $addr !== '') {
                    return; // ✅ sessionに配送先があるのでOK
                }
            }

            // ② sessionに無ければ profile をチェック
            $profile = $this->user()?->profile;

            if (!$profile || empty($profile->postal_code) || empty($profile->address)) {
                $validator->errors()->add('shipping', '配送先が未設定です。配送先を設定してください。');
            }
        });
    }
}