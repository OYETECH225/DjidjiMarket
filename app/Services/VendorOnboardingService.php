<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Validation\ValidationException;

class VendorOnboardingService
{
    /**
     * @param  array{business_name: string, vendor_type: string, slug: string, description?: ?string, address_text?: ?string, latitude?: ?float, longitude?: ?float}  $data
     */
    public function createProfile(User $user, array $data): Vendor
    {
        if ($user->vendor()->exists()) {
            throw ValidationException::withMessages([
                'business_name' => ['Un profil vendeur existe déjà pour ce compte.'],
            ]);
        }

        return $user->vendor()->create([
            ...$data,
            'verification_level' => 'non_verifie',
        ]);
    }
}
