<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
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
            'name' => $this->name,
            'value' => $this->value,
            "image" => $this->image,
            "start_date" => $this->start_date,
            'end_date' => $this->end_date,
            "status" => $this->status,
            "customer_id" => $this->customer_id,
            "quantity" => $this->quantity
        ];
    }
}
