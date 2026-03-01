<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'body' => ['required', 'string', 'max:255' ],
        ];
    }

    public function messages() : array
    {
        return [
            'body.required' => 'コメントを入力してください',
            'body.max' => 'コメントは２５５文字以内で入力してください',
        ];
    }
}
