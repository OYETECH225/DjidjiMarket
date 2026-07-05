<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Home;
use App\Livewire\MyOrders;
use App\Livewire\Profile;
use App\Livewire\VendorStorefront;
use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountAndNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_can_be_filtered_by_vendor_type(): void
    {
        $boutiqueUser = User::factory()->create(['role' => 'vendor']);
        Vendor::create([
            'user_id' => $boutiqueUser->id, 'business_name' => 'La Boutique', 'vendor_type' => 'boutique',
            'slug' => 'la-boutique', 'is_active' => true,
        ]);
        $restaurantUser = User::factory()->create(['role' => 'vendor']);
        Vendor::create([
            'user_id' => $restaurantUser->id, 'business_name' => 'Le Restaurant', 'vendor_type' => 'restaurant',
            'slug' => 'le-restaurant', 'is_active' => true,
        ]);

        Livewire::test(Home::class)
            ->assertSee('La Boutique')
            ->assertSee('Le Restaurant')
            ->call('filterBy', 'restaurant')
            ->assertSee('Le Restaurant')
            ->assertDontSee('La Boutique');
    }

    public function test_home_ignores_invalid_vendor_type_filter(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        Vendor::create([
            'user_id' => $vendorUser->id, 'business_name' => 'La Boutique', 'vendor_type' => 'boutique',
            'slug' => 'la-boutique', 'is_active' => true,
        ]);

        Livewire::test(Home::class)
            ->call('filterBy', 'not-a-type')
            ->assertSet('type', null)
            ->assertSee('La Boutique');
    }

    public function test_storefront_can_be_filtered_by_listing_type(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id, 'business_name' => 'Chez Awa', 'vendor_type' => 'restaurant',
            'slug' => 'chez-awa', 'is_active' => true,
        ]);
        Listing::create(['vendor_id' => $vendor->id, 'type' => 'plat_du_jour', 'name' => 'Attieke poisson', 'price' => 2000, 'is_active' => true]);
        Listing::create(['vendor_id' => $vendor->id, 'type' => 'menu_item', 'name' => 'Jus de bissap', 'price' => 500, 'is_active' => true]);

        Livewire::test(VendorStorefront::class, ['slug' => $vendor->slug])
            ->assertSee('Attieke poisson')
            ->assertSee('Jus de bissap')
            ->call('filterBy', 'plat_du_jour')
            ->assertSee('Attieke poisson')
            ->assertDontSee('Jus de bissap');
    }

    public function test_my_orders_only_lists_the_authenticated_clients_orders(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $otherClient = User::factory()->create(['role' => 'client']);
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id, 'business_name' => 'La Boutique', 'vendor_type' => 'boutique',
            'slug' => 'la-boutique', 'is_active' => true,
        ]);

        Order::create([
            'client_id' => $client->id, 'vendor_id' => $vendor->id,
            'delivery_address_text' => 'Cocody', 'total_amount' => 5000, 'commission_amount' => 500,
        ]);
        Order::create([
            'client_id' => $otherClient->id, 'vendor_id' => $vendor->id,
            'delivery_address_text' => 'Yopougon', 'total_amount' => 3000, 'commission_amount' => 300,
        ]);

        Livewire::actingAs($client)
            ->test(MyOrders::class)
            ->assertSee('La Boutique')
            ->assertSee('5 000');

        $this->assertSame(1, $client->orders()->count());
    }

    public function test_guest_cannot_access_my_orders_or_profile(): void
    {
        $this->get('/mes-commandes')->assertRedirect('/connexion');
        $this->get('/profil')->assertRedirect('/connexion');
    }

    public function test_profile_shows_account_info_and_role_specific_link(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor', 'name' => 'Awa Kone']);
        Vendor::create([
            'user_id' => $vendorUser->id, 'business_name' => 'La Boutique', 'vendor_type' => 'boutique',
            'slug' => 'la-boutique', 'is_active' => true,
        ]);

        Livewire::actingAs($vendorUser)
            ->test(Profile::class)
            ->assertSee('Awa Kone')
            ->assertSee('Vendeur')
            ->assertSee('Mon espace vendeur');
    }

    public function test_bottom_nav_shown_on_home_and_storefront_but_not_on_cart(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id, 'business_name' => 'La Boutique', 'vendor_type' => 'boutique',
            'slug' => 'la-boutique', 'is_active' => true,
        ]);
        $navMarker = 'fixed inset-x-0 bottom-0';

        $this->get('/')->assertSee($navMarker, false);
        $this->get("/boutique/{$vendor->slug}")->assertSee($navMarker, false);
        $this->get('/panier')->assertDontSee($navMarker, false);
    }
}
