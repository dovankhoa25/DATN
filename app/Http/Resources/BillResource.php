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
            // 'user_id' => $this->user_id ? new UserResource($this->user) : null,
            // 'customer_id' => $this->customer_id,
            'khachhang' => $this->user_id ? new UserResource($this->user) : ($this->customer_id ?? null),
            'addresses' => $this->UserAddress ? $this->UserAddress->address : null,
            'order_date' => $this->order_date,
            'total_amount' => $this->total_amount,
            'branch_address' => $this->branch_address,
            // 'payment' => $this->payment ? new PaymentResource($this->payment) : null,
            'payment' => $this->payment ? $this->payment->name : null,
            'voucher' => $this->voucher ? $this->voucher->value : null,
            'note' => $this->note,
            'order_type' => $this->order_type,
            // 'products' =>  BillDetailResource::collection($this->billDetails),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
