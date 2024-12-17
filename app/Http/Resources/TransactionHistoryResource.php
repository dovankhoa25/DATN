<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'gateway' => $this->gateway,
            'transaction_date' => $this->transaction_date,
            'account_number' => $this->account_number,
            'code' => $this->code,
            'content' => $this->content,
            'transfer_type' => $this->transfer_type,
            'transfer_amount' => $this->transfer_amount,
            'accumulated' => $this->accumulated,
            'sub_account' => $this->sub_account,
            'reference_code' => $this->reference_code,
            'description' => $this->description,
        ];
    }
}
