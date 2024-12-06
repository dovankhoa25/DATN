<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends BaseApiRequest
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
            'name' => 'string|nullable',
            'api_key' => 'string|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Tên  phải là chuỗi ký tự.',
            'api_key.string' => 'Tên api phải là chuỗi ký tự.',
        ];
    }
}
