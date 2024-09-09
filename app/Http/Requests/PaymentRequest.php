<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends BaseApiRequest
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
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => "Tên hình thức thanh toán là bắt buộc",
            'name.string' => "Tên hình thức thanh toán phải là một chuỗi",
            'name.max' => "Tên hình thức thanh toán tối đa 255 kí tự",

        ];
    }
}
