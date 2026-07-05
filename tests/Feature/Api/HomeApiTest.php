<?php

namespace Tests\Feature\Api;

use App\Models\Listing;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeVendor(bool $active = true): Vendor
    {
        $user = User::factory()->create(['role' => 'vendor']);

        return Vendor::create([
            'user_id' => $user->id, 'business_name' => 'La Boutique', 'vendor_type' => 'boutique',
            'slug' => 'la-boutique-'.uniqid(), 'is_active' => $active,
        ]);
    }

    public function test_dishes_of_the_day_only_lists_active_dishes_from_active_vendors(): void
    {
        $activeVendor = $this->makeVendor();
        Listing::create(['vendor_id' => $activeVendor->id, 'type' => 'plat_du_jour', 'name' => 'Riz gras', 'price' => 1500, 'is_active' => true]);
        Listing::create(['vendor_id' => $activeVendor->id, 'type' => 'plat_du_jour', 'name' => 'Plat inactif', 'price' => 1000, 'is_active' => false]);
        Listing::create(['vendor_id' => $activeVendor->id, 'type' => 'produit', 'name' => 'Sac de riz', 'price' => 5000, 'is_active' => true]);

        $inactiveVendor = $this->makeVendor(active: false);
        Listing::create(['vendor_id' => $inactiveVendor->id, 'type' => 'plat_du_jour', 'name' => 'Plat fantome', 'price' => 1200, 'is_active' => true]);

        $response = $this->getJson('/api/dishes-of-the-day');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Riz gras'));
        $this->assertFalse($names->contains('Plat inactif'));
        $this->assertFalse($names->contains('Sac de riz'));
        $this->assertFalse($names->contains('Plat fantome'));
        $this->assertSame('La Boutique', collect($response->json('data'))->firstWhere('name', 'Riz gras')['vendor_business_name']);
    }

    public function test_flash_sales_only_lists_active_unexpired_sales_from_active_vendors(): void
    {
        $activeVendor = $this->makeVendor();
        Listing::create([
            'vendor_id' => $activeVendor->id, 'type' => 'produit', 'name' => 'Robe wax', 'price' => 15000,
            'sale_price' => 9000, 'sale_ends_at' => now()->addHours(2), 'is_active' => true,
        ]);
        Listing::create([
            'vendor_id' => $activeVendor->id, 'type' => 'produit', 'name' => 'Vente expirée', 'price' => 8000,
            'sale_price' => 5000, 'sale_ends_at' => now()->subHour(), 'is_active' => true,
        ]);

        $inactiveVendor = $this->makeVendor(active: false);
        Listing::create([
            'vendor_id' => $inactiveVendor->id, 'type' => 'produit', 'name' => 'Vente fantome', 'price' => 8000,
            'sale_price' => 4000, 'sale_ends_at' => now()->addHours(2), 'is_active' => true,
        ]);

        $response = $this->getJson('/api/flash-sales');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Robe wax'));
        $this->assertFalse($names->contains('Vente expirée'));
        $this->assertFalse($names->contains('Vente fantome'));

        $robeWax = collect($response->json('data'))->firstWhere('name', 'Robe wax');
        $this->assertTrue($robeWax['is_on_flash_sale']);
        $this->assertEquals(9000, $robeWax['effective_price']);
    }
}
