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
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean']
        ];
    }

    public function messages()
    {
        return [
            'address.required' => 'Địa chỉ là bắt buộc',
            'address.max' => 'Địa chỉ nhiều nhất là 255 kí tự',

            'city.required' => 'Địa chỉ là bắt buộc',
            'city.max' => 'Địa chỉ nhiều nhất là 255 kí tự',

            'state.required' => 'Địa chỉ là bắt buộc',
            'state.max' => 'Địa chỉ nhiều nhất là 255 kí tự',

            'postal_code.required' => 'Địa chỉ là bắt buộc',
            'postal_code.max' => 'Địa chỉ nhiều nhất là 255 kí tự',

            'country.required' => 'Địa chỉ là bắt buộc',
            'country.max' => 'Địa chỉ nhiều nhất là 255 kí tự',
        ];
    }
}
