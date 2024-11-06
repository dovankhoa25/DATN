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
            'khachhang' => $this->user_id ? new UserResource($this->user) : ($this->customer_id ?? null),
            // 'addresses' => $this->user_addresses_id ? $this->user->addresses->address : null,
            'order_date' => $this->order_date,
            'total_amount' => $this->total_amount,
            'branch_address' => $this->branch_address,
            'payment' => $this->payment ? $this->payment->name : null,
            'voucher' => $this->voucher ? $this->voucher->value : null,
            'note' => $this->note,
            'order_type' => $this->order_type,
            'table_number' => $this->table_number,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'qr_expiration' => $this->qr_expiration,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
