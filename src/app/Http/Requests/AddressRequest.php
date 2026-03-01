<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth ミドルウェアで守ってる前提
    }

    public function rules(): array
    {
        return [
            // 例: 123-4567（8文字）
            'postal_code' => ['required', 'regex:/^\d{3}-\d{4}$/'],

            'address'     => ['required', 'string', 'max:255'],

            'building'    => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'postal_code.required' => '郵便番号を入力してください',
            'postal_code.regex'    => '郵便番号は「123-4567」の形式で入力してください',
            'address.required'     => '住所を入力してください',
            'address.max'          => '住所は255文字以内で入力してください',
            'building.max'         => '建物名は255文字以内で入力してください',
        ];
    }
}