<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Foundation\Http\FormRequest;


class CustomerRequest extends BaseApiRequest
{
    public function authorize()
    {
        return true; // Cho phép request này được thực hiện
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone_number' => ['required'],
            'diemthuong' => 'nullable|integer',
            'user_id' => 'nullable|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên là bắt buộc.',
            'name.string' => 'Tên phải là một chuỗi ký tự.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            
            'phone_number.required' => 'Số điện thoại là bắt buộc.',
            'phone_number.unique' => 'Số điện thoại đã tồn tại.',
            'phone_number.regex' => 'Số điện thoại không hợp lệ.',
            
            'diemthuong.integer' => 'Điểm thưởng phải là một số nguyên.',
            
            'user_id.exists' => 'ID người dùng phải tồn tại trong bảng người dùng.',
        ];
    }
}
