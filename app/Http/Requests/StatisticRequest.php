<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatisticRequest extends BaseApiRequest
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
            'year' => 'integer|min:2000|max:' . date('Y'),
            'month' => 'nullable|integer|min:1|max:12', // Chỉ dùng khi thống kê theo ngày
            'quarter' => 'nullable|integer|min:1|max:4', // Chỉ dùng khi thống kê theo quý
        ];
    }

    public function messages()
    {
        return [
            'year.required' => 'Vui lòng nhập năm thống kê.',
            'year.integer' => 'Năm phải là số nguyên.',
            'month.integer' => 'Tháng phải là số nguyên.',
            'quarter.integer' => 'Quý phải là số nguyên.',
        ];
    }
}
