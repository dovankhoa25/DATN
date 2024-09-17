<?php

namespace App\Http\Requests\Bill;

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
            'ma_bill'        => 'nullable|string',
            'order_date'     => 'nullable|date',
            'order_type'     => 'nullable|in:dine-in,take-away', // Giả định 2 loại order
            'status'         => 'nullable|in:pending,confirmed,preparing,shipping,completed,cancelled,failed', // Giả định các status
            'table_number'   => 'nullable|integer',
            'branch_address' => 'nullable|string',
            'per_page' => 'integer|min:1|max:100'
        ];
    }

    public function filters()
    {
        return $this->only([
            'ma_bill',
            'order_date',
            'order_type',
            'status',
            'table_number',
            'branch_address',
        ]);
    }

}
