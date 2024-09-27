<?php

namespace App\Http\Requests\Product;


use Illuminate\Foundation\Http\FormRequest;

class FilterProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => 'integer|min:1|max:100',
            'sort_by' => 'string|nullable',
            'orderby' => 'in:asc,desc|nullable',
            'name' => 'string|nullable',
            'status' => 'boolean|nullable',
            'description' => 'string|nullable',
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.integer' => 'Số lượng bản ghi mỗi trang phải là số nguyên.',
            'per_page.min' => 'Số lượng bản ghi tối thiểu phải là 1.',
            'per_page.max' => 'Số lượng bản ghi tối đa không được vượt quá 100.',
            'sort_by.string' => 'Sắp xếp theo cột phải là chuỗi ký tự.',
            'orderby.in' => 'Kiểu sắp xếp chỉ có thể là asc hoặc desc.',
            'name.string' => 'Tên sản phẩm phải là chuỗi ký tự.',
            'description.string' => 'Mô tả sản phẩm phải là chuỗi ký tự.',
            'start_date.date' => 'Ngày bắt đầu phải là định dạng ngày hợp lệ.',
            'end_date.date' => 'Ngày kết thúc phải là định dạng ngày hợp lệ.',
        ];
    }
}
