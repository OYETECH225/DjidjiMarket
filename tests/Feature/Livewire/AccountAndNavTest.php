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
use App\Services\CartService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    public function test_home_featured_vendors_only_shows_verified_active_vendors(): void
    {
        $verifiedUser = User::factory()->create(['role' => 'vendor']);
        Vendor::create([
            'user_id' => $verifiedUser->id, 'business_name' => 'Boutique Verifiee', 'vendor_type' => 'boutique',
            'slug' => 'boutique-verifiee', 'is_active' => true, 'verification_level' => 'verifie',
        ]);
        $unverifiedUser = User::factory()->create(['role' => 'vendor']);
        Vendor::create([
            'user_id' => $unverifiedUser->id, 'business_name' => 'Boutique Non Verifiee', 'vendor_type' => 'boutique',
            'slug' => 'boutique-non-verifiee', 'is_active' => true, 'verification_level' => 'non_verifie',
        ]);

        // Both vendors appear in the general directory further down the page —
        // only the featured section (marked by the VÉRIFIÉ badge) is scoped
        // to verified vendors, and no fake rating text is ever rendered.
        Livewire::test(Home::class)
            ->assertSee('Boutique Verifiee')
            ->assertSee('VÉRIFIÉ')
            ->assertDontSee('avis');
    }

    public function test_home_search_matches_vendor_and_listing_names(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id, 'business_name' => 'Chez Tantie Awa', 'vendor_type' => 'restaurant',
            'slug' => 'chez-tantie-awa', 'is_active' => true,
        ]);
        Listing::create(['vendor_id' => $vendor->id, 'type' => 'produit', 'name' => 'Attieke poisson', 'price' => 2000, 'is_active' => true]);

        Livewire::test(Home::class)
            ->set('query', 'awa')
            ->assertSee('Chez Tantie Awa')
            ->set('query', 'attieke')
            ->assertSee('Attieke poisson');
    }

    public function test_home_shows_only_active_dishes_of_the_day_from_active_vendors(): void
    {
        $activeVendorUser = User::factory()->create(['role' => 'vendor']);
        $activeVendor = Vendor::create([
            'user_id' => $activeVendorUser->id, 'business_name' => 'Chez Fatou', 'vendor_type' => 'restaurant',
            'slug' => 'chez-fatou', 'is_active' => true,
        ]);
        Listing::create(['vendor_id' => $activeVendor->id, 'type' => 'plat_du_jour', 'name' => 'Riz gras', 'price' => 1500, 'is_active' => true]);
        Listing::create(['vendor_id' => $activeVendor->id, 'type' => 'plat_du_jour', 'name' => 'Plat inactif', 'price' => 1000, 'is_active' => false]);
        Listing::create(['vendor_id' => $activeVendor->id, 'type' => 'produit', 'name' => 'Sac de riz', 'price' => 5000, 'is_active' => true]);

        $inactiveVendorUser = User::factory()->create(['role' => 'vendor']);
        $inactiveVendor = Vendor::create([
            'user_id' => $inactiveVendorUser->id, 'business_name' => 'Boutique fermée', 'vendor_type' => 'restaurant',
            'slug' => 'boutique-fermee', 'is_active' => false,
        ]);
        Listing::create(['vendor_id' => $inactiveVendor->id, 'type' => 'plat_du_jour', 'name' => 'Plat fantome', 'price' => 1200, 'is_active' => true]);

        Livewire::test(Home::class)
            ->assertSee('Riz gras')
            ->assertDontSee('Plat inactif')
            ->assertDontSee('Sac de riz')
            ->assertDontSee('Plat fantome');
    }

    public function test_add_dish_of_the_day_to_cart(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id, 'business_name' => 'Chez Fatou', 'vendor_type' => 'restaurant',
            'slug' => 'chez-fatou', 'is_active' => true,
        ]);
        $dish = Listing::create(['vendor_id' => $vendor->id, 'type' => 'plat_du_jour', 'name' => 'Riz gras', 'price' => 1500, 'is_active' => true]);

        Livewire::test(Home::class)
            ->call('addDishToCart', $dish->id)
            ->assertSet('addedMessage', '"Riz gras" ajouté au panier.');

        $this->assertSame(1, app(CartService::class)->count());
    }

    public function test_home_shows_only_active_flash_sales_from_active_vendors(): void
    {
        $activeVendorUser = User::factory()->create(['role' => 'vendor']);
        $activeVendor = Vendor::create([
            'user_id' => $activeVendorUser->id, 'business_name' => 'La Boutique', 'vendor_type' => 'boutique',
            'slug' => 'la-boutique', 'is_active' => true,
        ]);
        Listing::create([
            'vendor_id' => $activeVendor->id, 'type' => 'produit', 'name' => 'Robe wax', 'price' => 15000,
            'sale_price' => 9000, 'sale_ends_at' => now()->addHours(2), 'is_active' => true,
        ]);
        Listing::create([
            'vendor_id' => $activeVendor->id, 'type' => 'produit', 'name' => 'Vente expirée', 'price' => 8000,
            'sale_price' => 5000, 'sale_ends_at' => now()->subHour(), 'is_active' => true,
        ]);
        Listing::create(['vendor_id' => $activeVendor->id, 'type' => 'produit', 'name' => 'Sac a main', 'price' => 8000, 'is_active' => true]);

        $inactiveVendorUser = User::factory()->create(['role' => 'vendor']);
        $inactiveVendor = Vendor::create([
            'user_id' => $inactiveVendorUser->id, 'business_name' => 'Boutique fermée', 'vendor_type' => 'boutique',
            'slug' => 'boutique-fermee', 'is_active' => false,
        ]);
        Listing::create([
            'vendor_id' => $inactiveVendor->id, 'type' => 'produit', 'name' => 'Vente fantome', 'price' => 8000,
            'sale_price' => 4000, 'sale_ends_at' => now()->addHours(2), 'is_active' => true,
        ]);

        Livewire::test(Home::class)
            ->assertSee('Robe wax')
            ->assertDontSee('Vente expirée')
            ->assertDontSee('Sac a main')
            ->assertDontSee('Vente fantome');
    }

    public function test_add_flash_sale_item_to_cart_charges_the_sale_price(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id, 'business_name' => 'La Boutique', 'vendor_type' => 'boutique',
            'slug' => 'la-boutique', 'is_active' => true,
        ]);
        $listing = Listing::create([
            'vendor_id' => $vendor->id, 'type' => 'produit', 'name' => 'Robe wax', 'price' => 15000,
            'sale_price' => 9000, 'sale_ends_at' => now()->addHours(2), 'is_active' => true,
        ]);

        Livewire::test(Home::class)
            ->call('addFlashSaleToCart', $listing->id)
            ->assertSet('addedMessage', '"Robe wax" ajouté au panier.');

        $this->assertSame(9000.0, app(CartService::class)->total());
    }

    public function test_cannot_add_expired_flash_sale_item_to_cart(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id, 'business_name' => 'La Boutique', 'vendor_type' => 'boutique',
            'slug' => 'la-boutique', 'is_active' => true,
        ]);
        $listing = Listing::create([
            'vendor_id' => $vendor->id, 'type' => 'produit', 'name' => 'Robe wax', 'price' => 15000,
            'sale_price' => 9000, 'sale_ends_at' => now()->subHour(), 'is_active' => true,
        ]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(Home::class)->call('addFlashSaleToCart', $listing->id);
    }

    public function test_cannot_add_non_dish_listing_via_add_dish_to_cart(): void
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id, 'business_name' => 'La Boutique', 'vendor_type' => 'boutique',
            'slug' => 'la-boutique', 'is_active' => true,
        ]);
        $listing = Listing::create(['vendor_id' => $vendor->id, 'type' => 'produit', 'name' => 'Robe wax', 'price' => 15000, 'is_active' => true]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(Home::class)->call('addDishToCart', $listing->id);
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
