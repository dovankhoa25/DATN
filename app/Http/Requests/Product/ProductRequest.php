<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseApiRequest;

class ProductRequest extends BaseApiRequest
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
        if ($this->isMethod('post')) {
            return [
                // Product
                'name' => 'required|string|max:255',
                'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'status' => 'boolean',
                'category_id' => 'required|exists:categories,id',

                // Product Detail
                'product_details' => 'required|array',
                'product_details.*.size_id' => 'required|exists:sizes,id',
                'product_details.*.price' => 'required|numeric|min:0',
                'product_details.*.quantity' => 'required|integer|min:1',
                'product_details.*.sale' => 'nullable|numeric|min:0',
                'product_details.*.status' => 'nullable|boolean',

                // Image
                'product_details.*.images.*.file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ];
        }

        if ($this->isMethod('put')) {
            return [
                // Product
                'name' => 'required|string|max:255',
                'thumbnail' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'status' => 'boolean',
                'category_id' => 'required|exists:categories,id',

                //Product Detail
                'product_details' => 'required|array',
                'product_details.*.size_id' => 'required|exists:sizes,id',
                'product_details.*.price' => 'required|numeric|min:0',
                'product_details.*.quantity' => 'required|integer|min:1',
                'product_details.*.sale' => 'nullable|numeric|min:0',
                'product_details.*.status' => 'nullable|boolean',

                // Image
                'product_details.*.images' => 'nullable|required_without:product_details.*.image_old|array',
                'product_details.*.images.*.file' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                //Image_old
                'product_details.*.image_old' => 'nullable|required_without:product_details.*.images|array',
                'product_details.*.image_old.*' => 'exists:images,id',
            ];
        }
    }



    public function messages(): array
    {
        return [
            // Product Messages
            'name.required' => 'Tên sản phẩm là bắt buộc.',
            'name.string' => 'Tên sản phẩm phải là chuỗi ký tự.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',

            'thumbnail.required' => 'Ảnh đại diện là bắt buộc.',
            'thumbnail.max' => 'Đường dẫn ảnh đại diện không được vượt quá 255 ký tự.',

            'status.boolean' => 'Trạng thái sản phẩm phải là kiểu boolean.',

            'sub_categories_id.required' => 'Danh mục con là bắt buộc.',
            'sub_categories_id.exists' => 'Danh mục con không tồn tại.',

            'product_details.required' => 'Chi tiết sản phẩm là bắt buộc.',
            'product_details.array' => 'Chi tiết sản phẩm phải là một mảng.',

            'product_details.*.size_id.required' => 'Size là bắt buộc.',
            'product_details.*.size_id.exists' => 'Size không tồn tại trong hệ thống.',

            'product_details.*.price.required' => 'Giá sản phẩm là bắt buộc.',
            'product_details.*.price.numeric' => 'Giá sản phẩm phải là một số.',
            'product_details.*.price.min' => 'Giá sản phẩm không được nhỏ hơn 0.',

            'product_details.*.quantity.required' => 'Số lượng sản phẩm là bắt buộc.',
            'product_details.*.quantity.integer' => 'Số lượng sản phẩm phải là một số nguyên.',
            'product_details.*.quantity.min' => 'Số lượng sản phẩm phải lớn hơn 0.',

            'product_details.*.sale.numeric' => 'Giá khuyến mãi phải là một số.',
            'product_details.*.sale.min' => 'Giá khuyến mãi không được nhỏ hơn 0.',

            // Image Messages
            'product_details.*.images.required' => 'Hình ảnh là bắt buộc.',
            'product_details.*.images.array' => 'Hình ảnh phải là một mảng.',

        ];
    }
}
