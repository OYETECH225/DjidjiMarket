<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    /**
     * Public-facing vendor profile — deliberately excludes internal-only
     * fields (RCCM/DFE numbers, verification documents, commission_rate).
     */
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
        ];
    }
}
