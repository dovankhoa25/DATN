<?php

namespace App\Http\Requests\Table;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class TableRequest extends BaseApiRequest
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
            'table' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_guest' => 'nullable|integer|min:1',
            'max_guest' => 'nullable|integer|min:1|gte:min_guest',
            'deposit' => 'nullable|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'table.required' => 'Trường "table" là bắt buộc.',
            'table.string' => 'Trường "table" phải là một chuỗi văn bản.',

            'description.string' => 'Trường "description" phải là một chuỗi văn bản.',

            'min_guest.integer' => 'Trường "min_guest" phải là một số nguyên.',
            'min_guest.min' => 'Trường "min_guest" phải có giá trị tối thiểu là 1.',

            'max_guest.integer' => 'Trường "max_guest" phải là một số nguyên.',
            'max_guest.min' => 'Trường "max_guest" phải có giá trị tối thiểu là 1.',
            'max_guest.gte' => 'Trường "max_guest" phải lớn hơn hoặc bằng giá trị của "min_guest".',

            'deposit.numeric' => 'Trường "deposit" phải là một số.',
            'deposit.min' => 'Trường "deposit" phải có giá trị tối thiểu là 0.',
        ];
    }
}
