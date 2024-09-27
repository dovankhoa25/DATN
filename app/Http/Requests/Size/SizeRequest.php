<?php

namespace App\Http\Requests\Size;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class SizeRequest extends BaseApiRequest
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
            'name.required' => "Tên size là bắt buộc",
            'name.string' => "Tên size phải là một chuỗi",
            'name.max' => "Tên size tối đa 255 kí tự",


        ];
    }
}
