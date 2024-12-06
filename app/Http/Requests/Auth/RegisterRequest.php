<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Foundation\Http\FormRequest;


class RegisterRequest extends BaseApiRequest
{
    public function authorize()
    {
        return true; // Cho phép request này được thực hiện
    }

    public function rules()
    {
        return [
            'name' => 'nullable|string|max:50|min:5',
            'phone_number' => 'required|phone|unique:customers,phone_number',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages()
    {
        return [

            'name.string' => 'name phải là 1 chuỗi',
            'name.max' => 'name nhiều nhất 50 kí tự',
            'name.min' => 'name phải ít nhất có 5 kí tự',

            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email phải là một địa chỉ email hợp lệ.',
            'email.unique' => 'Email này đã được sử dụng.',

            'phone_number.required' => 'phone_number là bắt buộc.',
            'phone_number.phone_number' => 'phone_number phải là một địa chỉ phone_number hợp lệ.',
            'phone_number.unique' => 'phone_number này đã được sử dụng.',


            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ];
    }
}
