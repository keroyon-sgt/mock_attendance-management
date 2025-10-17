<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return false;
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'unique:users', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '名を入力してください',
            // 'name.string' => '名前は文字列で入力してください',
            // 'name.max' => '名前は255文字以下で入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'email.unique:users' => 'そのメールアドレスは、すでに使われています。',
            // 'email.string' => 'メールアドレスは文字列で入力してください',
            'email.email' => 'メールアドレスはメール形式で入力してください',
            // 'email.max' => 'メールアドレスは255文字以下で入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }
}
