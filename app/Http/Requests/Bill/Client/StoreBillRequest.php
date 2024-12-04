<?php

namespace App\Http\Requests\Bill\Client;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreBillRequest extends BaseApiRequest
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
            'cart_items' => 'required|array|min:1',
            'cart_items.*' => 'required|integer',
            'use_points' => 'boolean',
            'vouchers' => 'nullable|array',
            'vouchers.*' => 'exists:vouchers,id',
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
            'cart_items.required' => 'Bạn cần chọn ít nhất một sản phẩm.',
            'cart_items.*.required' => 'ID giỏ hàng là bắt buộc.',
            'cart_items.*.exists' => 'Giỏ hàng không tồn tại hoặc đã bị xóa.',
            'user_addresses_id.required' => 'Địa chỉ người dùng là bắt buộc.',
            'user_addresses_id.exists' => 'Địa chỉ người dùng không hợp lệ.',
            'payment_id.required' => 'Phương thức thanh toán là bắt buộc.',
            'payment_id.exists' => 'Phương thức thanh toán không hợp lệ.',
            'order_type.in' => 'Loại đơn hàng không hợp lệ.',
            'note.max' => 'Ghi chú không được vượt quá 255 ký tự.',
        ];
    }
}
