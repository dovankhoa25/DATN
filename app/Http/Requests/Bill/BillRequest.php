<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BillRequest extends BaseApiRequest
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
            'user_id' => 'required|exists:users,id',
            'order_date' => 'required|date',
            'total_money' => 'required|numeric',
            'address' => 'required|string|max:255',
            'payment_id' => 'required|exists:payments,id',
            'voucher_id' => 'nullable|exists:vouchers,id',
            'note' => 'nullable|string',
            'status' => 'in:pending,completed',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'id user phải là bắt buộc',
            'user_id.exists' => 'id user phải tồn tại trong bảng users',

            'order_date.exists' => 'order date phải là bắt buộc',
            'order_date.date' => 'order date phải đúng định dạng',

            'total_money.required' => 'total money phải là bắt buộc',
            'total_money.numeric' => 'total money phải đúng định dạng',

            'address.required' => 'address phải là bắt buộc',

            'payment_id.required' => 'id payment phải là bắt buộc',
            'payment_id.exists' => 'id payment phải tồn tại trong bảng payments',

            'voucher_id.required' => 'id voucher phải là bắt buộc',
            'voucher_id.exists' => 'id voucher phải tồn tại trong bảng vouchers',
        ];
    }
}
