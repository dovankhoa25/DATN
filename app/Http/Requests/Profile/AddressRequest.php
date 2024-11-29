<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends BaseApiRequest
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
            'fullname' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9]{10,15}$/'],
            'province' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'commune' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean']
        ];
    }

    public function messages()
    {
        return [
            'fullname.required' => 'Họ và tên là bắt buộc.',
            'fullname.string' => 'Họ và tên phải là một chuỗi ký tự.',
            'fullname.max' => 'Họ và tên không được vượt quá 255 ký tự.',

            'phone.required' => 'Số điện thoại là bắt buộc.',
            'phone.regex' => 'Số điện thoại không hợp lệ. Vui lòng nhập số điện thoại từ 10 đến 15 chữ số.',

            'province.required' => 'Tỉnh thành là bắt buộc.',
            'province.string' => 'Tỉnh thành phải là một chuỗi ký tự.',
            'province.max' => 'Tỉnh thành không được vượt quá 255 ký tự.',

            'district.required' => 'Quận huyện là bắt buộc.',
            'district.string' => 'Quận huyện phải là một chuỗi ký tự.',
            'district.max' => 'Quận huyện không được vượt quá 255 ký tự.',

            'commune.required' => 'Xã phường là bắt buộc.',
            'commune.string' => 'Xã phường phải là một chuỗi ký tự.',
            'commune.max' => 'Xã phường không được vượt quá 255 ký tự.',

            'address.required' => 'Địa chỉ là bắt buộc.',
            'address.string' => 'Địa chỉ phải là một chuỗi ký tự.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',

            'postal_code.string' => 'Mã bưu điện phải là một chuỗi ký tự.',
            'postal_code.max' => 'Mã bưu điện không được vượt quá 255 ký tự.',

            'country.string' => 'Quốc gia phải là một chuỗi ký tự.',
            'country.max' => 'Quốc gia không được vượt quá 255 ký tự.',

            'is_default.boolean' => 'Trường mặc định phải là giá trị boolean (true/false).',
        ];
    }
}
