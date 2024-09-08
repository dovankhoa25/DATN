<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class SubcategoryRequest extends BaseApiRequest
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
            'category_id' => 'required|exists:categories,id'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Nếu category_id được gửi trong request
            if ($this->has('category_id')) {
                $categoryId = $this->input('category_id');

                // Lấy category có id bằng category_id
                $category = Category::find($categoryId);

                // Kiểm tra xem category_id có phải là danh mục con của một danh mục khác hay không
                if ($category && $category->parent_id !== null) {
                    // Nếu category_id không phải là danh mục gốc (có parent_id khác null), đưa ra lỗi
                    $validator->errors()->add('category_id', 'Danh mục cha không được là danh mục con của danh mục khác.');
                }
            }
        });
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

            'category_id.required' => 'id category phải bắt buộc',
            'category_id.exists' => 'id category phải tồn tại trong bảng categories',
        ];
    }
}
