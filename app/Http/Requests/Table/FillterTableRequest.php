<?php

namespace App\Http\Requests\Table;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class FillterTableRequest extends BaseApiRequest
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
            'table' => 'string|nullable',
            'sort_by' => 'string|nullable',
            'orderby' => 'in:asc,desc|nullable',
            'status' => 'boolean|nullable',
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
            'status.boolean' => 'Trạng thái phải là kiểu boolean (0 hoặc 1).'
        ];
    }
}
