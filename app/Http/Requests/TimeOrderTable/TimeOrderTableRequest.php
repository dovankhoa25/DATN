<?php

namespace App\Http\Requests\TimeOrderTable;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class TimeOrderTableRequest extends BaseApiRequest
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
            'table_id' => 'required|exists:tables,id',
            'user_id' => 'exists:users,id',
            'phone_number' => ['required', 'regex:/^[0-9]{10,15}$/'],
            'date_oder' => 'required|date|after_or_equal:today',
            'time_oder' => 'required|in:sáng,trưa,tối',
            'description' => 'nullable|string',
            'status' => 'in:pending,completed,failed',
        ];
    }

    public function messages()
    {
        return [
            'table_id.required' => 'table_id là bắt buộc',
            'table_id.exists' => 'table_id phải nằm trong bảng tables',

            'user_id.exists' => 'user_id phải nằm trong bảng users',

            'phone_number.required' => 'phone_number là bắt buộc',
            'phone_number.regex' => 'phone_number phải đúng định dạng',
            'phone_number.digits_between' => 'phone_number phải đúng định dạng',

            'date_oder.required' => 'date_oder là bắt buộc',
            'date_oder.date' => 'date_oder phải đúng định dạng',
            'date_oder.after_or_equal' => 'date_oder phải là hôm nay hoặc ngày trong tương lai',

            'time_oder.required' => 'time_oder là bắt buộc',
            'time_oder.in' => 'time_oder phải là sáng hoặc trưa hoặc tối',

            'description.string' => 'description phải là chuỗi string',
        ];
    }
}
