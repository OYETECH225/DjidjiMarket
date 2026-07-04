<?php

namespace Tests\Feature\Api;

use App\Models\Listing;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_vendor_role_can_create_profile(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        Sanctum::actingAs($client);

        $response = $this->postJson('/api/vendor/profile', [
            'business_name' => 'Boutique X',
            'vendor_type' => 'boutique',
            'slug' => 'boutique-x',
        ]);

        $response->assertForbidden();
    }

    public function test_vendor_can_create_profile_only_once(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        Sanctum::actingAs($vendorUser);

        $payload = [
            'business_name' => 'Boutique X',
            'vendor_type' => 'boutique',
            'slug' => 'boutique-x',
        ];

        $this->postJson('/api/vendor/profile', $payload)->assertCreated();
        $this->postJson('/api/vendor/profile', array_merge($payload, ['slug' => 'boutique-x-2']))
            ->assertStatus(422);
    }

    public function test_public_show_hides_internal_fields_and_only_returns_active_vendor(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Boutique Active',
            'vendor_type' => 'boutique',
            'slug' => 'boutique-active',
            'verification_level' => 'verifie',
            'rccm_number' => 'CI-SECRET-123',
            'commission_rate' => 12,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/vendors/{$vendor->slug}");

        $response->assertOk();
        $response->assertJsonMissingPath('data.rccm_number');
        $response->assertJsonMissingPath('data.commission_rate');
        $response->assertJsonMissingPath('data.user_id');
        $response->assertJsonPath('data.business_name', 'Boutique Active');

        $vendorUser2 = User::factory()->create(['role' => 'vendor']);
        $inactiveVendor = Vendor::create([
            'user_id' => $vendorUser2->id,
            'business_name' => 'Boutique Inactive',
            'vendor_type' => 'boutique',
            'slug' => 'boutique-inactive',
            'verification_level' => 'non_verifie',
            'is_active' => false,
        ]);

        $this->getJson("/api/vendors/{$inactiveVendor->slug}")->assertNotFound();
    }

    public function test_index_only_lists_active_vendors(): void
    {
        $activeUser = User::factory()->create(['role' => 'vendor']);
        Vendor::create([
            'user_id' => $activeUser->id, 'business_name' => 'Active', 'vendor_type' => 'boutique',
            'slug' => 'active', 'is_active' => true,
        ]);
        $inactiveUser = User::factory()->create(['role' => 'vendor']);
        Vendor::create([
            'user_id' => $inactiveUser->id, 'business_name' => 'Inactive', 'vendor_type' => 'boutique',
            'slug' => 'inactive', 'is_active' => false,
        ]);

        $response = $this->getJson('/api/vendors');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('business_name');
        $this->assertTrue($names->contains('Active'));
        $this->assertFalse($names->contains('Inactive'));
    }

    public function test_public_listings_only_returns_active_listings(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Boutique Active',
            'vendor_type' => 'boutique',
            'slug' => 'boutique-active',
            'verification_level' => 'verifie',
            'is_active' => true,
        ]);

        Listing::create([
            'vendor_id' => $vendor->id, 'type' => 'produit', 'name' => 'Visible', 'price' => 1000, 'is_active' => true,
        ]);
        Listing::create([
            'vendor_id' => $vendor->id, 'type' => 'produit', 'name' => 'Caché', 'price' => 1000, 'is_active' => false,
        ]);

        $response = $this->getJson("/api/vendors/{$vendor->id}/listings");

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Visible'));
        $this->assertFalse($names->contains('Caché'));
    }
}
