<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // 'id' => $this->id,
            // 'bill_id' => $this->bill_id,
            'id' => $this->productDetail->id,
            'name' => $this->productDetail->product->name, 
            'thumbnail' => $this->productDetail->product->thumbnail ? $this->productDetail->product->thumbnail : "",
            'size' => $this->productDetail->size->name,

            'quantity' => $this->quantity,
            'price' => $this->productDetail->price,
            'total' => $this->productDetail->price * $this->quantity,

            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            // 
        ];
    }


}
