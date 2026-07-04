<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle_type' => $this->vehicle_type,
            'verification_status' => $this->verification_status,
            'is_available' => $this->is_available,
            'rating_average' => $this->rating_average,
        ];
    }
}
