<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OnlCartRequest extends BaseApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_detail_id' => ['required', 'exists:product_details,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => "Thông tin user là bắt buộc",
            'user_id.integer' => "User id là số nguyên",
            'user_id.exists' => "Thông tin user không tồn tại",

            'product_detail_id.required' => "Sản phẩm là bắt buộc",
            'product_detail_id.exists' => "Sản phẩm phải tồn tại và chưa bị xóa trong dữ liệu",

            'quantity.required' => "Số lượng là bắt buộc",
            'quantity.integer' => "Số lượng là một số nguyên",
            'quantity.min' => "Số lượng không nhỏ hơn 1",

            'price.required' => "Số lượng là bắt buộc",
            'price.integer' => "Số lượng là một số nguyên",
            'price.min' => "Số lượng không nhỏ hơn 1",
        ];
    }
}
