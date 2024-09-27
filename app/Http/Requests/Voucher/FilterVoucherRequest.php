<?php

namespace App\Http\Requests\Voucher;

use Illuminate\Foundation\Http\FormRequest;

class FilterVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'value' => 'nullable|numeric|min:0',
            'expiration_date' => 'nullable|date|after_or_equal:today',
            'user_id' => 'nullable|exists:users,id', 
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Tên voucher phải là một chuỗi ký tự hợp lệ.',
            'value.numeric' => 'Giá trị voucher phải là số.',
            'expiration_date.date' => 'Ngày hết hạn phải là một ngày hợp lệ.',
            'expiration_date.after_or_equal' => 'Ngày hết hạn phải không được trước ngày hôm nay.',
            'user_id.exists' => 'ID người dùng không tồn tại.',
        ];
    }
}
