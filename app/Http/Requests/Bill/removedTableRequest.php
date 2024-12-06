<?php

namespace App\Http\Requests\Bill;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class removedTableRequest extends BaseApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'tableIds' => 'required|array|min:1',
            'tableIds.*' => 'integer|exists:tables,id',
            'bill_id' => 'required|integer|exists:bills,id',
        ];
    }

    public function messages()
    {
        return [
            'tableIds.required' => 'Danh sách bàn không được để trống.',
            'tableIds.array' => 'Danh sách bàn phải là một mảng.',
            'tableIds.min' => 'Danh sách bàn phải có ít nhất một bàn.',
            'tableIds.*.integer' => 'Mỗi ID bàn phải là một số nguyên.',
            'tableIds.*.exists' => 'ID bàn không tồn tại trong cơ sở dữ liệu.',
            'bill_id.required' => 'ID hóa đơn không được để trống.',
            'bill_id.integer' => 'ID hóa đơn phải là một số nguyên.',
            'bill_id.exists' => 'ID hóa đơn không tồn tại trong cơ sở dữ liệu.',
        ];
    }
}
