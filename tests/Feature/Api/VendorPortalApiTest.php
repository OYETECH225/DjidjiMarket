<?php

namespace Tests\Feature\Api;

use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorPortalApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeVendor(float $commissionRate = 10): array
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Boutique Test',
            'vendor_type' => 'boutique',
            'slug' => 'boutique-test-'.uniqid(),
            'commission_rate' => $commissionRate,
            'is_active' => true,
        ]);

        return [$vendorUser, $vendor];
    }

    public function test_me_returns_owner_only_fields(): void
    {
        [$vendorUser, $vendor] = $this->makeVendor(commissionRate: 12);
        Sanctum::actingAs($vendorUser);

        $response = $this->getJson('/api/vendor/me');

        $response->assertOk();
        $response->assertJsonPath('data.commission_rate', '12.00');
        $response->assertJsonPath('data.is_active', true);
    }

    public function test_me_returns_404_without_a_vendor_profile(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        Sanctum::actingAs($vendorUser);

        $this->getJson('/api/vendor/me')->assertNotFound();
    }

    public function test_update_me_toggles_visibility(): void
    {
        [$vendorUser, $vendor] = $this->makeVendor();
        Sanctum::actingAs($vendorUser);

        $this->patchJson('/api/vendor/me', ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse($vendor->fresh()->is_active);
    }

    public function test_vendor_can_create_list_update_and_delete_own_listings(): void
    {
        [$vendorUser, $vendor] = $this->makeVendor();
        Sanctum::actingAs($vendorUser);

        $create = $this->postJson('/api/vendor/listings', [
            'type' => 'produit',
            'name' => 'Robe wax',
            'price' => 15000,
        ]);
        $create->assertCreated();
        $create->assertJsonPath('listing.currency', 'XOF');
        $create->assertJsonPath('listing.is_active', true);
        $listingId = $create->json('listing.id');

        $this->getJson('/api/vendor/listings')
            ->assertOk()
            ->assertJsonPath('data.0.id', $listingId);

        $this->putJson("/api/vendor/listings/{$listingId}", ['price' => 18000])
            ->assertOk()
            ->assertJsonPath('data.price', '18000.00');

        $this->deleteJson("/api/vendor/listings/{$listingId}")->assertOk();
        $this->assertDatabaseMissing('listings', ['id' => $listingId]);
    }

    public function test_vendor_cannot_update_or_delete_another_vendors_listing(): void
    {
        [, $vendorA] = $this->makeVendor();
        [$vendorUserB] = $this->makeVendor();

        $listing = Listing::create([
            'vendor_id' => $vendorA->id, 'type' => 'produit', 'name' => 'A', 'price' => 1000,
        ]);

        Sanctum::actingAs($vendorUserB);

        $this->putJson("/api/vendor/listings/{$listing->id}", ['price' => 5000])->assertForbidden();
        $this->deleteJson("/api/vendor/listings/{$listing->id}")->assertForbidden();

        $this->assertDatabaseHas('listings', ['id' => $listing->id, 'price' => 1000]);
    }

    public function test_vendor_orders_only_shows_own_orders(): void
    {
        [$vendorUserA, $vendorA] = $this->makeVendor();
        [, $vendorB] = $this->makeVendor();
        $client = User::factory()->create(['role' => 'client']);

        $orderA = Order::create([
            'client_id' => $client->id, 'vendor_id' => $vendorA->id,
            'delivery_address_text' => 'X', 'total_amount' => 1000,
        ]);
        Order::create([
            'client_id' => $client->id, 'vendor_id' => $vendorB->id,
            'delivery_address_text' => 'X', 'total_amount' => 2000,
        ]);

        Sanctum::actingAs($vendorUserA);

        $response = $this->getJson('/api/vendor/orders');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertEquals([$orderA->id], $ids->all());
    }
}
