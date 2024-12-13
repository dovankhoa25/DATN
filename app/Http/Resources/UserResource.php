<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->customer->phone,
            'is_locked' => $this->is_locked,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
        ];
    }
}
