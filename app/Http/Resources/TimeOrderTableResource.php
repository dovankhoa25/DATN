<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeOrderTableResource extends JsonResource
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
            'table_id ' => $this->table_id,
            'user_id ' => $this->user_id,
            'phone_number ' => $this->phone_number,
            'date_oder ' => $this->date_oder,
            'time_oder ' => $this->time_oder,
            'description ' => $this->description,
            'status ' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
