<?php

namespace App\Http\Requests\Voucher;

use App\Http\Requests\BaseApiRequest;


class VoucherRequest extends BaseApiRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'value' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'max_discount_value' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|boolean',
            'customer_id' => 'nullable|exists:customers,id',
        ];

        if ($this->isMethod('put')) {
            $rules['value'] = 'nullable|numeric|min:0';
            $rules['quantity'] = 'required|integer|min:0';
        }

        return $rules;
    }


    public function messages()
    {
        return [
            'name.required' => 'Tên voucher là bắt buộc.',
            'name.string' => 'Tên voucher phải là một chuỗi văn bản.',
            'name.max' => 'Tên voucher không được vượt quá 255 ký tự.',

            'value.required' => 'Giá trị voucher là bắt buộc khi không có tỷ lệ giảm giá.',
            'value.numeric' => 'Giá trị voucher phải là một số.',
            'value.min' => 'Giá trị voucher phải lớn hơn hoặc bằng 0.',

            'discount_percentage.numeric' => 'Tỷ lệ giảm giá phải là một số.',
            'discount_percentage.min' => 'Tỷ lệ giảm giá phải lớn hơn hoặc bằng 0.',
            'discount_percentage.max' => 'Tỷ lệ giảm giá không được vượt quá 100.',

            'max_discount_value.numeric' => 'Giá trị giảm giá tối đa phải là một số.',
            'max_discount_value.min' => 'Giá trị giảm giá tối đa phải lớn hơn hoặc bằng 0.',

            'image.string' => 'Hình ảnh phải là một chuỗi văn bản.',

            'start_date.required' => 'Ngày bắt đầu là bắt buộc.',
            'start_date.date' => 'Ngày bắt đầu phải là một ngày hợp lệ.',
            'start_date.before_or_equal' => 'Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.',

            'end_date.required' => 'Ngày kết thúc là bắt buộc.',
            'end_date.date' => 'Ngày kết thúc phải là một ngày hợp lệ.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',

            'status.boolean' => 'Trạng thái phải là giá trị boolean.',

            'customer_id.exists' => 'Khách hàng này không tồn tại.',

            'quantity.required' => 'Số lượng voucher là bắt buộc.',
            'quantity.integer' => 'Số lượng phải là một số nguyên.',
            'quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 0.',
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $value = $this->input('value');
            $discountPercentage = $this->input('discount_percentage');
            $maxDiscountValue = $this->input('max_discount_value');
            // Kiểm tra nếu tất cả 3 giá trị đều là 0
            if ($value == 0 && $discountPercentage == 0 && $maxDiscountValue == 0) {
                $validator->errors()->add('value', 'Bạn phải nhập giá trị cho ít nhất một trong các trường: `value`, `discount_percentage` hoặc `max_discount_value`.');
            }

            if ((is_null($value) && is_null($discountPercentage)) ||
                ($value > 0 && $discountPercentage > 0)
            ) {
                $validator->errors()->add('value', 'Chỉ được nhập giá trị giảm giá (`value`) hoặc tỷ lệ giảm giá (`discount_percentage`), không được nhập cả hai.');
                $validator->errors()->add('discount_percentage', 'Chỉ được nhập giá trị giảm giá (`value`) hoặc tỷ lệ giảm giá (`discount_percentage`), không được nhập cả hai.');
            }

            if (!is_null($discountPercentage) && $discountPercentage > 0 && is_null($this->input('max_discount_value'))) {
                $validator->errors()->add('max_discount_value', 'Cần nhập giá trị giảm giá tối đa (`max_discount_value`) khi sử dụng tỷ lệ giảm giá.');
            }
        });
    }
}
