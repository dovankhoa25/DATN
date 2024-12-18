<?php

namespace App\Http\Resources\Client;

use App\Http\Resources\UserResource;
use App\Models\BillDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillOrderResource extends JsonResource
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
            // 'addresses' => $this->userAddress ? $this->userAddress->address : null,
            'order_date' => $this->order_date,
            'total_amount' => $this->total_amount,
            'branch_address' => $this->branch_address,
            'payment' => $this->payment ? $this->payment->name : null,
            'voucher' => $this->voucher ? $this->voucher->value : null,
            'note' => $this->note,
            'order_type' => $this->order_type,
            'table_number' => $this->tables->pluck('table')->toArray(),
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'qr_expiration' => $this->qr_expiration,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'bill_details' => $this->billDetails->groupBy('productDetail.product_id')->map(function ($details, $productId) {
                $firstDetail = $details->first();
                return [

                    'product' => [
                        'id' => $firstDetail->productDetail->product->id,
                        'name' => $firstDetail->productDetail->product->name,
                        'thumbnail' => $firstDetail->productDetail->product->thumbnail,
                        'description' => $firstDetail->productDetail->product->description,
                        'status' => $firstDetail->productDetail->product->status,
                        'product_details' => $details->map(function ($detail) {
                            return [
                                'bill_detail_id' => $detail->id,
                                'id' => $detail->productDetail->id,
                                'size_name' => $detail->productDetail->size->name,
                                'price' => $detail->productDetail->price,
                                'quantity' => $detail->quantity,
                                'sale' => $detail->productDetail->sale,
                                'status' => $detail->status,
                                'time_order' => $detail->created_at,
                            ];
                        }),
                    ],

                    'quantity' => $details->sum('quantity'),
                    'total_price' => $details->sum(fn($detail) => $detail->price * $detail->quantity),
                ];
            })->values(),
        ];
    }
}
