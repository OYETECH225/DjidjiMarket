<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'stock_quantity' => $this->stock_quantity,
            'available_from' => $this->available_from,
            'available_until' => $this->available_until,
            'photo_urls' => $this->photo_urls,
            'display_number' => $this->display_number,
        ];
    }
}
