<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ma_bill'=>$this->ma_bill,
            'product_detail_id'=>$this->product_detail_id,
            'quantity'=>$this->quantity
        ];
    }
}
