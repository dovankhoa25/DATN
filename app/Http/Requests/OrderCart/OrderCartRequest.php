<?php

namespace App\Http\Requests\OrderCart;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class OrderCartRequest extends BaseApiRequest
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
        if ($this->isMethod('post')) {
            return [
                'ma_bill' => ['required', 'string', 'max:255', 'exists:bills,ma_bill'],
                'product_detail_id' => ['required', 'exists:product_details,id'],
                'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            ];
        }

        if ($this->isMethod('put')) {
            return [
                'quantity' => ['required', 'integer', 'min:1', 'max:50'],
                'id_cart_order' => ['required', 'numeric', 'exists:oder_cart,id'],
            ];
        }
    }

    public function messages()
    {
        return [
            'id_cart_order.required' => "id cart là bắt buộc",
            'id_cart_order.numeric' => "id là số",
            'id_cart_order.exists' => "không tồn tại món ăn",

            'quantity.required' => "Số lượng là bắt buộc",
            'quantity.integer' => "Số lượng là một số nguyên",
            'quantity.min' => "Số lượng lớn hơn 1",
            'quantity.max' => "Số lượng nhỏ hơn 10",
        ];
    }
}
