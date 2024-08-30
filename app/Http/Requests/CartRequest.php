<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends BaseApiRequest
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
            'ma_bill' => ['required', 'string', 'max:255', 'exists:bills,ma_bill'],
            'product_detail_id' => ['required', 'exists:product_details,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages()
    {
        return [
            'ma_bill.required' => "Mã bill là bắt buộc",
            'ma_bill.string' => "Mã bill là chuỗi kí tự",
            'ma_bill.max' => "Mã bill có độ dài tối đa là 255",
            'ma_bill.exists' => "Mã bill không tồn tại",

            'product_detail_id.required' => "Sản phẩm là bắt buộc",
            'product_detail_id.exists' => "Sản phẩm phải tồn tại và chưa bị xóa trong dữ liệu",

            'quantity.required' => "Số lượng là bắt buộc",
            'quantity.integer' => "Số lượng là một số nguyên",
            'quantity.min' => "Số lượng không nhỏ hơn 1",
        ];
    }
}
