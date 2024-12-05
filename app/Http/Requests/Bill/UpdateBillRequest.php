<?php

namespace App\Http\Requests\Bill;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBillRequest extends FormRequest
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
            'status' => 'required|in:confirmed,preparing,shipping,completed,failed,cancellation_approved,cancellation_rejected',
            'shiper_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
