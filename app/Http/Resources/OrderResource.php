<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'vendor_id' => $this->vendor_id,
            'courier_id' => $this->courier_id,
            'status' => $this->status,
            'is_final' => $this->isFinal(),
            'delivery_latitude' => $this->delivery_latitude,
            'delivery_longitude' => $this->delivery_longitude,
            'delivery_address_text' => $this->delivery_address_text,
            'total_amount' => $this->total_amount,
            'delivery_fee' => $this->delivery_fee,
            'commission_amount' => $this->commission_amount,
            'source' => $this->source,
            'vendor_business_name' => $this->whenLoaded('vendor', fn () => $this->vendor->business_name),
            'vendor_address_text' => $this->whenLoaded('vendor', fn () => $this->vendor->address_text),
            'vendor_latitude' => $this->whenLoaded('vendor', fn () => $this->vendor->latitude),
            'vendor_longitude' => $this->whenLoaded('vendor', fn () => $this->vendor->longitude),
            'client_name' => $this->whenLoaded('client', fn () => $this->client->name),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
        ];
    }
}
