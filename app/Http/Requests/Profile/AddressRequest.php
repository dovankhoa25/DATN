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
            'commune' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:255'],
            'country' => ['string', 'max:255'],
            'is_default' => ['nullable', 'boolean']
        ];
    }

    public function messages()
    {
        return [
            'fullname.required' => 'Địa chỉ là bắt buộc',
            'fullname.max' => 'Địa chỉ nhiều nhất là 255 kí tự',

            'phone.required' => 'Địa chỉ là bắt buộc',
            'phone.regex' => 'Số điện thoại không đúng định dạng',

            'commune.required' => 'Địa chỉ là bắt buộc',
            'commune.max' => 'Địa chỉ nhiều nhất là 255 kí tự',

            'address.required' => 'Địa chỉ là bắt buộc',
            'address.max' => 'Địa chỉ nhiều nhất là 255 kí tự',

            'city.required' => 'Địa chỉ là bắt buộc',
            'city.max' => 'Địa chỉ nhiều nhất là 255 kí tự',

            'postal_code.required' => 'Địa chỉ là bắt buộc',
            'postal_code.max' => 'Địa chỉ nhiều nhất là 255 kí tự',

            'country.required' => 'Địa chỉ là bắt buộc',
            'country.max' => 'Địa chỉ nhiều nhất là 255 kí tự',
        ];
    }
}
