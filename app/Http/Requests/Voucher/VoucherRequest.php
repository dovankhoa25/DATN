<?php

namespace App\Http\Requests\Voucher;

use App\Http\Requests\BaseApiRequest;


class VoucherRequest extends BaseApiRequest
{

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
                'name' => 'required|string|max:255',
                'value' => 'required|numeric|min:0',
                'image' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'status' => 'nullable|boolean',
                'customer_id' => 'nullable|exists:customers,id',
                'quantity' => 'required|numeric|min:0',
            ];
        }

        if ($this->isMethod('put')) {
            return [
                'name' => 'required|string|max:255',
                'value' => 'required|numeric|min:0',
                'image' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'status' => 'nullable|boolean',
                'customer_id' => 'nullable|exists:customers,id',
                'quantity' => 'required|numeric|min:0',
            ];
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên voucher là bắt buộc.',
            'name.string' => 'Tên voucher phải là một chuỗi văn bản.',
            'name.max' => 'Tên voucher không được vượt quá 255 ký tự.',

            'value.required' => 'Giá trị voucher là bắt buộc.',
            'value.numeric' => 'Giá trị voucher phải là một số.',
            'value.min' => 'Giá trị voucher phải lớn hơn hoặc bằng 0.',

            'image.string' => 'Hình ảnh phải là một chuỗi văn bản.',

            'start_date.required' => 'Ngày bắt đầu là bắt buộc.',
            'start_date.date' => 'Ngày bắt đầu phải là một ngày hợp lệ.',

            'end_date.required' => 'Ngày kết thúc là bắt buộc.',
            'end_date.date' => 'Ngày kết thúc phải là một ngày hợp lệ.',

            'status.boolean' => 'Trạng thái phải là giá trị boolean.',

            'customer_id.exists' => 'Khách hàng này không tồn tại.',

            'quantity.required' => 'Số lượng voucher là bắt buộc.',
            'quantity.numeric' => 'Số lượng phải là một số.',
            'quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 0.',
        ];
    }
}
