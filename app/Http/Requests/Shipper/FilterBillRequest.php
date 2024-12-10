<?php

namespace App\Http\Requests\Shipper;

use Illuminate\Foundation\Http\FormRequest;

class FilterBillRequest extends FormRequest
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
            'per_page' => 'nullable|integer|min:1|max:100', // Giới hạn phân trang từ 1-100
            'status' => 'nullable|string|in:shipping,completed,failed',
        ];
    }
    public function messages()
    {
        return [
            'per_page.integer' => 'Giá trị per_page phải là số nguyên.',
            'per_page.min' => 'Số lượng phân trang phải lớn hơn hoặc bằng 1.',
            'per_page.max' => 'Số lượng phân trang không được vượt quá 100.',
            'status.in' => 'Trạng thái không hợp lệ. Chỉ được chọn các giá trị: pending, confirmed, preparing, shipping, completed, failed.',
        ];
    }
}
