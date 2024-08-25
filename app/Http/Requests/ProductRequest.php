<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends BaseApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Validation cho Product
            'name' => 'required|string|max:255',
            'thumbnail' => 'required|string|max:255',
            'status' => 'required|boolean',
            'sub_categories_id' => 'required|exists:sub_categories,id',
            
            // Validation cho Product Detail
            'product_details' => 'required|array',
            'product_details.*.size_id' => 'required|exists:sizes,id',
            'product_details.*.price' => 'required|numeric|min:0',
            'product_details.*.quantity' => 'required|integer|min:1',
            'product_details.*.sale' => 'nullable|numeric|min:0',
            'product_details.*.status' => 'required|boolean',
            
            // Validation cho Image
            'product_details.*.images' => 'required|array',
            'product_details.*.images.*.name' => 'required|string|max:255',
            'product_details.*.images.*.status' => 'required|boolean'
        ];
    }



    public function messages(): array
{
    return [
        // Product Messages
        'name.required' => 'Tên sản phẩm là bắt buộc.',
        'name.string' => 'Tên sản phẩm phải là chuỗi ký tự.',
        'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
        
        'thumbnail.required' => 'Ảnh đại diện là bắt buộc.',
        'thumbnail.string' => 'Đường dẫn ảnh đại diện phải là chuỗi ký tự.',
        'thumbnail.max' => 'Đường dẫn ảnh đại diện không được vượt quá 255 ký tự.',
        
        'status.required' => 'Trạng thái sản phẩm là bắt buộc.',
        'status.boolean' => 'Trạng thái sản phẩm phải là kiểu boolean.',
        
        'sub_categories_id.required' => 'Danh mục con là bắt buộc.',
        'sub_categories_id.exists' => 'Danh mục con không tồn tại.',
        
        // Product Detail Messages
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
        
        // 'product_details.*.status.required' => 'Trạng thái chi tiết sản phẩm là bắt buộc.',
        // 'product_details.*.status.boolean' => 'Trạng thái chi tiết sản phẩm phải là kiểu boolean.',
        
        // Image Messages
        'product_details.*.images.required' => 'Hình ảnh là bắt buộc.',
        'product_details.*.images.array' => 'Hình ảnh phải là một mảng.',
        
        'product_details.*.images.*.name.required' => 'Tên hình ảnh là bắt buộc.',
        'product_details.*.images.*.name.string' => 'Tên hình ảnh phải là chuỗi ký tự.',
        'product_details.*.images.*.name.max' => 'Tên hình ảnh không được vượt quá 255 ký tự.',
        
        // 'product_details.*.images.*.status.required' => 'Trạng thái hình ảnh là bắt buộc.',
        // 'product_details.*.images.*.status.boolean' => 'Trạng thái hình ảnh phải là kiểu boolean.'
    ];
}


}
