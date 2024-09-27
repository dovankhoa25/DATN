<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'thumbnail' => $this->thumbnail,
            'description' => $this->description,
            'status' => $this->status,
            'category' => [
                'id' =>  $this->category->id,
                'name' =>  $this->category->name,
            ],
            'product_details' => ProductDetailResource::collection($this->whenLoaded('productDetails')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

    }
}
