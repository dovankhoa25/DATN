<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseApiRequest;
use App\Models\Category;

class CategoryRequest extends BaseApiRequest
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
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'parent_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    $categoryId = request()->route('id');
                    if ($categoryId && $value == $categoryId) {
                        $fail('không được phép');
                    }
                },
            ],

        ];
    }


    public function messages()
    {
        return [
            'name.required' => 'Tên là bắt buộc.',
            'name.string' => 'Tên phải là một chuỗi ký tự.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',

            'image.image' => 'image phải là ảnh',
            'image.mimes' => 'image phải đúng định dạng',
            'image.max' => 'image phải < 2048mb',

            'parent_id.required' => 'id parent phải bắt buộc',
            'parent_id.exists' => 'id parent phải tồn tại trong bảng categories',
        ];
    }
}
