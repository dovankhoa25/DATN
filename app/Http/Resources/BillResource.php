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
            'order_date' => $this->order_date,
            'total_money' => $this->total_money,
            'address' => $this->address,
            'payment_id' => $this->payment_id,
            'voucher_id' => $this->voucher_id,
            'note' => $this->note,
            'products' => BillDetailResource::collection($this->billDetails),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
