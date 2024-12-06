<?php

namespace App\Http\Requests\Table;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class OpenTablesListRequest extends BaseApiRequest
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
    public function rules()
    {
        return [
            // 'user_id' => 'nullable|exists:users,id',
            'table_ids' => 'required|array|exists:tables,id',
            // 'customer_id' => 'nullable|exists:customers,id',
            // 'user_addresses_id' => 'nullable|exists:user_addresses,id',
            // 'order_date' => 'date|after_or_equal:today',
            // 'total_amount' => 'numeric|min:0',
            // 'branch_address' => 'nullable|string|max:255',
            // 'payment_id' => 'nullable|exists:payments,id',
            // 'voucher_id' => 'nullable|exists:vouchers,id',
            // 'note' => 'nullable|string',
            // 'order_type' => 'in:in_restaurant,online',
            // 'status' => 'in:pending,confirmed,preparing,shipping,completed,cancelled,failed',
        ];
    }

    public function messages()
    {
        return [
            'table_ids.exists' => 'Table ID không tồn tại',
            'table_ids.required' => 'Table ID là bắt buộc',
            'table_ids.array' => 'Table IDs là mảng',

            // 'user_id.exists' => 'User ID không tồn tại',

            // 'customer_id.exists' => 'Customer ID không tồn tại',

            // 'user_addresses_id.exists' => 'User Address ID không tồn tại',

            // 'order_date.after_or_equal' => 'Ngày mở bàn phải là hôm nay hoặc trong tương lai',

            // 'total_amount.numeric' => 'Tổng tiền phải là một số',

            // 'payment_id.required' => 'Phương thức thanh toán là bắt buộc',
            // 'payment_id.exists' => 'Phương thức thanh toán không tồn tại',

            // 'voucher_id.exists' => 'Voucher không tồn tại',

            // 'order_type.in' => 'Loại đơn hàng không hợp lệ',

            // 'status.in' => 'Trạng thái không hợp lệ',
        ];
    }
}
