<?php

namespace App\Http\Resources\Shipper;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BillCollection extends ResourceCollection
{

    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'data' => $this->collection->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'ma_bill' => $bill->ma_bill,
                    'khachhang' => $bill->user_id ? new UserResource($bill->user) : ($bill->customer_id ?? null),
                    'order_date' => $bill->order_date,
                    'total_amount' => $bill->total_amount,
                    'branch_address' => $bill->branch_address,
                    'payment' => $bill->payment ? $bill->payment->name : null,
                    'address' => $bill->userAddress->full_address ?? null,
                    'note' => $bill->note,
                    'order_type' => $bill->order_type,
                    'table_number' => $bill->table_number,
                    'payment_status' => $bill->payment_status,
                    'status' => $bill->status,
                    'qr_expiration' => $bill->qr_expiration,
                    'created_at' => $bill->created_at,
                    'updated_at' => $bill->updated_at,
                    'vouchers' => $bill->vouchers->map(function ($voucher) {
                        return [
                            'id' => $voucher->id,
                            'name' => $voucher->name,
                        ];
                    }),
                    'tables' => $bill->tables->map(function ($table) {
                        return [
                            'id' => $table->id,
                            'name' => $table->name,
                        ];
                    }),
                    'shipping_histories' => $bill->shippingHistories->map(function ($history) {
                        return [
                            'id' => $history->id,
                            'event' => $history->event,
                            'description' => $history->description,
                            'image_url' => $history->image_url,
                            'admin' => $history->admin ? $history->admin->name : null,
                            'shipper' => $history->shipper ? $history->shipper->name : null,
                            'created_at' => $history->created_at,
                        ];
                    }),
                ];
            }),
            'pagination' => [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'total_pages' => $this->resource->lastPage(),
            ],
        ];
    }
}
