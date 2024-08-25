<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
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
            'size' => $this->size ? $this->size->name : null,  // Hiển thị tên size
            'price' => $this->price,
            'quantity' => $this->quantity,
            'product' => $this->product->name,  // Hiển thị tên sản phẩm
            'sale' => $this->sale,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
