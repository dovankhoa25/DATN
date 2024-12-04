<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->value,
            'discount_percentage' => $this->discount_percentage,
            'max_discount_value' => $this->max_discount_value,
            "image" => $this->image,
            "start_date" => $this->start_date,
            'end_date' => $this->end_date,
            "status" => $this->status,
            "customer_id" => $this->customer_id,
            "quantity" => $this->quantity
        ];
    }
}
