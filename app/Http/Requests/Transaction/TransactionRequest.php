<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
     * @return array<string, 
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer',
            'gateway' => 'required|string|max:255',
            'transactionDate' => 'required|date',
            'accountNumber' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'transferType' => 'required|in:in,out',
            'transferAmount' => 'required|integer',
            'accumulated' => 'required|integer',
            'subAccount' => 'nullable|string|max:255',
            'referenceCode' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
