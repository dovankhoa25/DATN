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
            'product_detail_id' => ['required', 'exists:product_details,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages()
    {
        return [

            'product_detail_id.required' => "Sản phẩm là bắt buộc",
            'product_detail_id.exists' => "Sản phẩm phải tồn tại và chưa bị xóa trong dữ liệu",

            'quantity.required' => "Số lượng là bắt buộc",
            'quantity.integer' => "Số lượng là một số nguyên",
            'quantity.min' => "Số lượng không nhỏ hơn 1",

        ];
    }
}
