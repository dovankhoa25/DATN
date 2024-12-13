<?php

namespace App\Http\Resources\Shipper;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BillCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'ma_bill' => $this->ma_bill,
            'khachhang' => $this->user_id ? new UserResource($this->user) : ($this->customer_id ?? null),
            // 'address' => $bill->userAddress->address ?? null,
            'order_date' => $this->order_date,
            'total_amount' => $this->total_amount,
            'branch_address' => $this->branch_address,
            'payment' => $this->payment ? $this->payment->name : null,
            // 'voucher' => $this->voucher ? $this->voucher->value : null,
            'vouchers' => $this->vouchers->map(function ($voucher) {
                return [
                    'id' => $voucher->id,
                    'name' => $voucher->name,
                ];
            }),
            'note' => $this->note,
            'order_type' => $this->order_type,
            'table_number' => $this->table_number,
            'tables' => $this->tables->map(function ($table) {
                return [
                    'id' => $table->id,
                    'name' => $table->name,
                ];
            }),
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'qr_expiration' => $this->qr_expiration,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
