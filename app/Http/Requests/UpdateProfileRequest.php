<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends BaseApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'name' => 'unique:users,name',
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8',
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Tên này đã được sử dụng.',

            'old_password.required' => 'Mật khẩu là bắt buộc.',
            'old_password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',

            'new_password.required' => 'Mật khẩu là bắt buộc.',
            'new_password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',

            'confirm_password.required' => 'Mật khẩu là bắt buộc.',
            'confirm_password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
        ];
    }
}
