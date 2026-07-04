<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Owner's view of their own vendor profile — unlike VendorResource (public),
 * this includes fields the vendor is entitled to see about their own
 * account but that shouldn't be exposed to the public API.
 */
class VendorProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_name' => $this->business_name,
            'vendor_type' => $this->vendor_type,
            'slug' => $this->slug,
            'description' => $this->description,
            'logo_url' => $this->logo_url,
            'cover_url' => $this->cover_url,
            'address_text' => $this->address_text,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'verification_level' => $this->verification_level,
            'commission_rate' => $this->commission_rate,
            'is_active' => $this->is_active,
        ];
    }
}
