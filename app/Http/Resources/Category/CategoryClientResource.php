<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryClientResource extends JsonResource
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
            // 'name_parent' => $this->parent ? $this->parent->name : null,
            'subcategory' => CategoryClientResource::collection($this->whenLoaded('subcategories')),
        ];
    }
}
