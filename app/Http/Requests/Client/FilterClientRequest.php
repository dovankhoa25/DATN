<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class FilterClientRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'per_page' => 'integer|min:1|max:100',
            'sort_by' => 'string|nullable',
            'orderby' => 'in:asc,desc|nullable',
            'name' => 'string|nullable',
            'api_key' => 'string|nullable',
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
            'name.string' => 'Tên  phải là chuỗi ký tự.',
            'api_key.string' => 'Tên api_key phải là chuỗi ký tự.',
        ];
    }
}
