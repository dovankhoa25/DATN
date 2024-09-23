<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ma_bill' => $this->ma_bill,
            'user_id' => $this->user_id,
            'customer_id' => $this->customer_id,
            'user_addresses_id' => $this->user_addresses_id,
            'order_date' => $this->order_date,
            'total_amount' => $this->total_amount,
            'branch_address' => $this->branch_address,
            'payment' => $this->payment ? new PaymentResource($this->payment) : null,
            'voucher_id' => $this->voucher_id,
            'note' => $this->note,
            'order_type' => $this->order_type,
            'products' =>  BillDetailResource::collection($this->billDetails),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
