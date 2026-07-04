<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'listing_id' => $this->listing_id,
            'listing_name' => $this->whenLoaded('listing', fn () => $this->listing->name),
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
        ];
    }
}
