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
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'customer_id' => 'nullable|exists:customers,id',
        ];
    }


    public function messages(): array
    {
        return [
            'name.string' => 'Tên voucher phải là một chuỗi ký tự hợp lệ.',
            'name.max' => 'Tên voucher không được vượt quá 255 ký tự.',
            'start_date.date' => 'Ngày bắt đầu phải là một ngày hợp lệ.',
            'end_date.date' => 'Ngày kết thúc phải là một ngày hợp lệ.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'customer_id.exists' => 'ID khách hàng không tồn tại.',
        ];
    }
}
