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
            'parent_id' => 'nullable|exists:categories,id',
        ];
    }

    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
    //         // Nếu category_id được gửi trong request
    //         if ($this->has('parent_id')) {
    //             $categoryId = $this->input('parent_id');

    //             // Lấy category có id bằng category_id
    //             $category = Category::find($categoryId);

    //             // Kiểm tra xem category_id có phải là danh mục con của một danh mục khác hay không
    //             if ($category && $category->parent_id !== null) {
    //                 // Nếu category_id không phải là danh mục gốc (có parent_id khác null), đưa ra lỗi
    //                 $validator->errors()->add('parent_id', 'Id parent này là danh mục con. Id parent phải là danh mục cha');
    //             }
    //         }
    //     });
    // }

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
