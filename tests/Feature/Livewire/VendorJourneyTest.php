<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Vendor\Dashboard;
use App\Livewire\Vendor\ListingForm;
use App\Livewire\Vendor\Listings;
use App\Livewire\Vendor\Onboarding;
use App\Livewire\Vendor\Orders;
use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VendorJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_vendor_role_can_access_onboarding(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)->get(route('vendor.onboarding'))->assertForbidden();
    }

    public function test_onboarding_creates_vendor_profile_and_redirects_to_dashboard(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);

        Livewire::actingAs($vendorUser)
            ->test(Onboarding::class)
            ->set('business_name', 'Boutique Awa')
            ->set('vendor_type', 'boutique')
            ->set('slug', 'boutique-awa')
            ->set('address_text', 'Cocody')
            ->call('create')
            ->assertRedirect(route('vendor.dashboard'));

        $this->assertDatabaseHas('vendors', [
            'user_id' => $vendorUser->id,
            'business_name' => 'Boutique Awa',
            'slug' => 'boutique-awa',
        ]);
    }

    public function test_onboarding_redirects_to_dashboard_if_profile_already_exists(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        Vendor::create([
            'user_id' => $vendorUser->id, 'business_name' => 'Existing', 'vendor_type' => 'boutique', 'slug' => 'existing',
        ]);

        Livewire::actingAs($vendorUser)
            ->test(Onboarding::class)
            ->assertRedirect(route('vendor.dashboard'));
    }

    public function test_dashboard_redirects_to_onboarding_without_a_profile(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);

        Livewire::actingAs($vendorUser)
            ->test(Dashboard::class)
            ->assertRedirect(route('vendor.onboarding'));
    }

    private function makeVendor(): array
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Boutique Awa',
            'vendor_type' => 'boutique',
            'slug' => 'boutique-awa-'.uniqid(),
            'is_active' => true,
        ]);

        return [$vendorUser, $vendor];
    }

    public function test_dashboard_shows_counts_and_can_toggle_active(): void
    {
        [$vendorUser, $vendor] = $this->makeVendor();
        Listing::create(['vendor_id' => $vendor->id, 'type' => 'produit', 'name' => 'A', 'price' => 1000]);

        Livewire::actingAs($vendorUser)
            ->test(Dashboard::class)
            ->assertSet('vendor.id', $vendor->id)
            ->assertSee('1')
            ->call('toggleActive');

        $this->assertFalse($vendor->fresh()->is_active);
    }

    public function test_vendor_can_create_a_listing(): void
    {
        [$vendorUser, $vendor] = $this->makeVendor();

        Livewire::actingAs($vendorUser)
            ->test(ListingForm::class)
            ->set('type', 'produit')
            ->set('name', 'Robe wax')
            ->set('price', '15000')
            ->call('save')
            ->assertRedirect(route('vendor.listings'));

        $this->assertDatabaseHas('listings', [
            'vendor_id' => $vendor->id,
            'name' => 'Robe wax',
            'currency' => 'XOF',
        ]);
    }

    public function test_vendor_can_create_a_listing_with_a_flash_sale(): void
    {
        [$vendorUser, $vendor] = $this->makeVendor();

        Livewire::actingAs($vendorUser)
            ->test(ListingForm::class)
            ->set('type', 'produit')
            ->set('name', 'Robe wax')
            ->set('price', '15000')
            ->set('sale_price', '9000')
            ->set('sale_ends_at', now()->addHours(2)->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertRedirect(route('vendor.listings'));

        $listing = Listing::where('vendor_id', $vendor->id)->where('name', 'Robe wax')->firstOrFail();
        $this->assertSame('9000.00', $listing->sale_price);
        $this->assertTrue($listing->isOnFlashSale());
        $this->assertSame(9000.0, $listing->effectivePrice());
    }

    public function test_flash_sale_price_must_be_lower_than_regular_price(): void
    {
        [$vendorUser] = $this->makeVendor();

        Livewire::actingAs($vendorUser)
            ->test(ListingForm::class)
            ->set('type', 'produit')
            ->set('name', 'Robe wax')
            ->set('price', '15000')
            ->set('sale_price', '15000')
            ->set('sale_ends_at', now()->addHours(2)->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['sale_price']);
    }

    public function test_flash_sale_requires_both_price_and_end_date(): void
    {
        [$vendorUser] = $this->makeVendor();

        Livewire::actingAs($vendorUser)
            ->test(ListingForm::class)
            ->set('type', 'produit')
            ->set('name', 'Robe wax')
            ->set('price', '15000')
            ->set('sale_price', '9000')
            ->call('save')
            ->assertHasErrors(['sale_ends_at']);
    }

    public function test_vendor_cannot_edit_another_vendors_listing(): void
    {
        [, $vendorA] = $this->makeVendor();
        [$vendorUserB] = $this->makeVendor();

        $listing = Listing::create(['vendor_id' => $vendorA->id, 'type' => 'produit', 'name' => 'A', 'price' => 1000]);

        Livewire::actingAs($vendorUserB)
            ->test(ListingForm::class, ['listing' => $listing])
            ->assertForbidden();
    }

    public function test_vendor_can_toggle_and_delete_own_listing_only(): void
    {
        [$vendorUserA, $vendorA] = $this->makeVendor();
        [$vendorUserB, $vendorB] = $this->makeVendor();

        $listingA = Listing::create(['vendor_id' => $vendorA->id, 'type' => 'produit', 'name' => 'A', 'price' => 1000, 'is_active' => true]);

        Livewire::actingAs($vendorUserA)
            ->test(Listings::class)
            ->call('toggleActive', $listingA->id);

        $this->assertFalse($listingA->fresh()->is_active);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($vendorUserB)
            ->test(Listings::class)
            ->call('delete', $listingA->id);
    }

    public function test_orders_page_only_shows_own_orders(): void
    {
        [$vendorUserA, $vendorA] = $this->makeVendor();
        [, $vendorB] = $this->makeVendor();
        $client = User::factory()->create(['role' => 'client']);

        $orderA = Order::create([
            'client_id' => $client->id, 'vendor_id' => $vendorA->id,
            'delivery_address_text' => 'X', 'total_amount' => 1000,
        ]);
        $orderB = Order::create([
            'client_id' => $client->id, 'vendor_id' => $vendorB->id,
            'delivery_address_text' => 'X', 'total_amount' => 2000,
        ]);

        Livewire::actingAs($vendorUserA)
            ->test(Orders::class)
            ->assertSee("Commande #{$orderA->id}")
            ->assertDontSee("Commande #{$orderB->id}");
    }
}
