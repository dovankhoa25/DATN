<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class FilterCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => 'integer|min:1|max:100',
            'name' => 'string|nullable',
            'email' => 'string|nullable',
            'phone' => 'string|nullable',
            'sort_by' => 'string|nullable',
            'orderby' => 'in:asc,desc|nullable'
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.integer' => 'Số lượng bản ghi mỗi trang phải là số nguyên.',
            'per_page.min' => 'Số lượng bản ghi tối thiểu phải là 1.',
            'per_page.max' => 'Số lượng bản ghi tối đa không được vượt quá 100.',
            'orderby.in' => 'Giá trị của orderby chỉ có thể là asc hoặc desc.',
            'sort_by.string' => 'Giá trị của sort_by phải là chuỗi ký tự hợp lệ.',
        ];
    }
}
