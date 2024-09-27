<?php

namespace App\Http\Requests\TimeOrderTable;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class FillterTimeOrderTableRequest extends BaseApiRequest
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
            'phone_number' => ['nullable', 'regex:/^[0-9]{10,15}$/'],
            'date_oder' => 'date|nullable',
            'sort_by' => 'string|nullable',
            'orderby' => 'in:asc,desc|nullable',
            'time_oder' => 'in:sáng,trưa,tối|nullable',
            'status' => 'in:pending,completed,failed|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.integer' => 'Số lượng bản ghi mỗi trang phải là số nguyên.',
            'per_page.min' => 'Số lượng bản ghi tối thiểu phải là 1.',
            'per_page.max' => 'Số lượng bản ghi tối đa không được vượt quá 100.',
            'orderby.in' => 'Giá trị của orderby chỉ có thể là asc hoặc desc.',
            'time_oder.in' => 'time_oder phải là sáng hoặc trưa hoặc tối',
            'sort_by.string' => 'Giá trị của sort_by phải là chuỗi ký tự hợp lệ.',
            'status.in' => 'Trạng thái phải là pending hoặc completed hoặc failed.',
            'date_oder.date' => 'Date order phải đúng định dạng'
        ];
    }
}
