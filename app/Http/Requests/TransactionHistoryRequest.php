<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionHistoryRequest extends FormRequest
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
            'per_page' => 'nullable|integer|min:1|max:100',


            'gateway' => 'nullable|string',
            'transaction_date' => 'nullable|date',
            'account_number' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'per_page.integer' => 'Số bản ghi mỗi trang phải là một số nguyên.',
            'per_page.min' => 'Số bản ghi mỗi trang phải lớn hơn hoặc bằng 1.',
            'per_page.max' => 'Số bản ghi mỗi trang phải nhỏ hơn hoặc bằng 100.',
        ];
    }
}
