<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VoucherRequest extends BaseApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // muốn check điều kiện thì check ở đây
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
            'name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'image' => 'nullable|string',
            'status' => 'nullable|boolean',
            'customer_id' => 'nullable|exists:customers,id',
            'expiration_date' => 'nullable|date|after_or_equal:today',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên voucher là bắt buộc.',
            'value.required' => 'Giá trị voucher là bắt buộc.',
            'value.numeric' => 'Giá trị voucher phải là số.',
            'value.min' => 'Giá trị voucher phải lớn hơn hoặc bằng 0.',
            'status.boolean' => 'Trạng thái voucher phải là true hoặc false.',
            'customer_id.exists' => 'Khách hàng không tồn tại.',
            'expiration_date.date' => 'Ngày hết hạn không hợp lệ.',
            'expiration_date.after_or_equal' => 'Ngày hết hạn phải là ngày hôm nay hoặc trong tương lai.',
        ];
    }

    
}