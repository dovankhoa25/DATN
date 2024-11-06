<?php

namespace App\Http\Requests\Bill\Client;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class BillOderRequest extends BaseApiRequest
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
            'ma_bill' => 'required|string|exists:bills,ma_bill',
        ];
    }

    public function messages(): array
    {
        return [
            'ma_bill.required' => 'Mã hóa đơn là bắt buộc.',
            'ma_bill.string' => 'Mã hóa đơn phải là chuỗi ký tự.',
            'ma_bill.exists' => 'Mã hóa đơn không tồn tại trong hệ thống.',
        ];
    }
}
