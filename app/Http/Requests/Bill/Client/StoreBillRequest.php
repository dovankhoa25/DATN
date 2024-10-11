<?php

namespace App\Http\Requests\Bill\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'productdetail_items' => 'required|array|min:1',
            'productdetail_items.*.product_detail_id' => 'required|exists:product_details,id',
            'productdetail_items.*.quantity' => 'required|integer|min:1',
            'use_points' => 'boolean',
            'voucher_id' => 'nullable|exists:vouchers,id',
            'user_addresses_id' => 'required|exists:user_addresses,id',
            'branch_address' => 'nullable|string|max:255',
            'payment_id' => 'required|exists:payments,id',
            'order_type' => 'in:online',
            'note' => 'nullable|string|max:255',
        ];
    }

    
    public function messages()
    {
        return [
            'productdetail_items.required' => 'Bạn cần chọn ít nhất một sản phẩm.',
            'productdetail_items.*.product_detail_id.required' => 'ID chi tiết sản phẩm là bắt buộc.',
            'productdetail_items.*.product_detail_id.exists' => 'Chi tiết sản phẩm không tồn tại.',
            'productdetail_items.*.quantity.required' => 'Số lượng là bắt buộc.',
            'productdetail_items.*.quantity.min' => 'Số lượng phải lớn hơn 0.',
            'user_addresses_id.required' => 'Địa chỉ người dùng là bắt buộc.',
            'user_addresses_id.exists' => 'Địa chỉ người dùng không hợp lệ.',
            'payment_id.required' => 'Phương thức thanh toán là bắt buộc.',
            'payment_id.exists' => 'Phương thức thanh toán không hợp lệ.',
            'order_type.in' => 'Loại đơn hàng không hợp lệ.',
        ];
    }


}
