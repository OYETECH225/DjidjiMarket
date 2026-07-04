<?php

namespace Tests\Feature;

use App\Filament\Resources\ListingResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\VendorResource;
use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentResourcesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get(VendorResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_vendor_resource_pages_load(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Boutique Test',
            'vendor_type' => 'boutique',
            'slug' => 'boutique-test',
            'verification_level' => 'non_verifie',
        ]);

        $this->actingAs($this->admin);

        $this->get(VendorResource::getUrl('index'))->assertOk();
        $this->get(VendorResource::getUrl('create'))->assertOk();
        $this->get(VendorResource::getUrl('edit', ['record' => $vendor]))->assertOk();
    }

    public function test_listing_resource_pages_load(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Boutique Test',
            'vendor_type' => 'boutique',
            'slug' => 'boutique-test',
            'verification_level' => 'non_verifie',
        ]);
        $listing = Listing::create([
            'vendor_id' => $vendor->id,
            'type' => 'produit',
            'name' => 'Telephone X',
            'price' => 150000,
            'display_number' => 1,
        ]);

        $this->actingAs($this->admin);

        $this->get(ListingResource::getUrl('index'))->assertOk();
        $this->get(ListingResource::getUrl('create'))->assertOk();
        $this->get(ListingResource::getUrl('edit', ['record' => $listing]))->assertOk();
    }

    public function test_order_resource_pages_load(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Boutique Test',
            'vendor_type' => 'boutique',
            'slug' => 'boutique-test',
            'verification_level' => 'non_verifie',
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $order = Order::create([
            'client_id' => $client->id,
            'vendor_id' => $vendor->id,
            'total_amount' => 150000,
            'source' => 'app',
        ]);

        $this->actingAs($this->admin);

        $this->get(OrderResource::getUrl('index'))->assertOk();
        $this->get(OrderResource::getUrl('create'))->assertOk();
        $this->get(OrderResource::getUrl('edit', ['record' => $order]))->assertOk();
    }
}
